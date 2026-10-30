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
     * Streaming variant — visitor sees the reply typed out token-by-token.
     * Emits Server-Sent Events: {token} chunks then a final {done, session_id}.
     */
    public function stream(Request $request, AiChatService $ai)
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

        return response()->stream(function () use ($ai, $validated, $history, $sessionId) {
            $full = '';

            try {
                foreach ($ai->streamReply($validated['message'], $history) as $token) {
                    $full .= $token;
                    echo 'data: '.json_encode(['token' => $token])."\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            } catch (\Throwable $e) {
                $full = $full ?: "Sorry, I'm having trouble responding right now. Please try again in a moment.";
                echo 'data: '.json_encode(['token' => $full])."\n\n";
            }

            ChatMessage::create(['session_id' => $sessionId, 'role' => 'assistant', 'message' => $full]);

            echo 'data: '.json_encode(['done' => true, 'session_id' => $sessionId])."\n\n";
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Visitor asks for a human — flags the session, pings all admins.
     */
    public function handoff(Request $request): JsonResponse
    {
        $data = $request->validate(['session_id' => 'required|string|max:64']);

        ChatMessage::where('session_id', $data['session_id'])->update(['handoff' => true]);
        ChatMessage::create([
            'session_id' => $data['session_id'],
            'role' => 'assistant',
            'message' => 'A team member has been notified and will reply here shortly. 💬',
        ]);

        foreach (\App\Models\User::role('admin')->pluck('id') as $adminId) {
            \App\Models\UserNotification::notify($adminId, 'contact', 'Visitor asked for a human agent',
                "Chat session " . substr($data['session_id'], 0, 8) . "…", route('tenant.chats.index', ['session' => $data['session_id']]));
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Visitor polls for staff replies — returns all messages for the session.
     */
    public function feed(Request $request): JsonResponse
    {
        $data = $request->validate(['session_id' => 'required|string|max:64']);

        return response()->json([
            'messages' => ChatMessage::where('session_id', $data['session_id'])
                ->oldest()->get(['role', 'message', 'created_at']),
        ]);
    }

    /**
     * Staff replies to a live visitor chat from the Chats inbox.
     */
    public function reply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_id' => 'required|string|max:64',
            'message' => 'required|string|max:2000',
        ]);

        ChatMessage::create([
            'session_id' => $data['session_id'],
            'role' => 'staff',
            'message' => $data['message'],
        ]);

        return response()->json(['status' => 'ok']);
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
            ->selectRaw('MAX(handoff) as handoff')
            ->groupBy('session_id')
            ->orderByDesc('last_at')
            ->paginate(15);

        $session = $request->query('session');

        $messages = $session
            ? ChatMessage::where('session_id', $session)->oldest()->get()
            : collect();

        return view('app.chats.index', compact('sessions', 'session', 'messages'));
    }

    /**
     * Delete an entire visitor chat session — admin only.
     */
    public function destroySession(string $sessionId)
    {
        ChatMessage::where('session_id', $sessionId)->delete();

        return redirect()->route('tenant.chats.index')->with('success', 'Conversation deleted.');
    }
}
