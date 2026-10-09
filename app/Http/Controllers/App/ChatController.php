<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Public chatbot endpoint — visitors ask questions,
     * AI answers using only this tenant's own data.
     */
    public function send(Request $request, AiChatService $ai): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $sessionId = $validated['session_id'] ?: (string) Str::uuid();

        $history = ChatMessage::where('session_id', $sessionId)
            ->latest()->take(10)->get()->reverse()
            ->map(fn (ChatMessage $m) => [
                'role' => $m->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m->message]],
            ])->values()->all();

        ChatMessage::create(['session_id' => $sessionId, 'role' => 'user', 'message' => $validated['message']]);

        $reply = $ai->reply($validated['message'], $history);

        ChatMessage::create(['session_id' => $sessionId, 'role' => 'assistant', 'message' => $reply]);

        return response()->json(['reply' => $reply, 'session_id' => $sessionId]);
    }

    /**
     * Staff view — browse visitor conversations grouped by session.
     */
    public function index(Request $request): View
    {
        $sessions = ChatMessage::query()
            ->select('session_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('MAX(created_at) as last_at')
            ->groupBy('session_id')
            ->orderByDesc('last_at')
            ->get();

        $session = $request->query('session');

        $messages = $session
            ? ChatMessage::where('session_id', $session)->oldest()->get()
            : collect();

        return view('app.chats.index', compact('sessions', 'session', 'messages'));
    }
}
