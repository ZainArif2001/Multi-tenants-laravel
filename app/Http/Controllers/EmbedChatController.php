<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Public embed API — external websites load the tenant's AI chatbot
 * via a single <script> tag and talk to these stateless endpoints.
 * Tenancy is initialized by ResolveEmbedTenant from the embed key.
 */
class EmbedChatController extends Controller
{
    /**
     * The self-contained chat widget JS — styles + logic baked in,
     * configured for this tenant (name, brand color, suggestions).
     */
    public function script(Request $request, string $key, AiChatService $ai)
    {
        return response(
            view('embed.widget', [
                'key' => $key,
                'tenantName' => tenant('name'),
                'color' => tenant('primary_color') ?: '#4f46e5',
                'suggestions' => $ai->suggestedQuestions(),
            ])->render(),
            200,
            ['Content-Type' => 'application/javascript']
        );
    }

    public function send(Request $request, string $key, AiChatService $ai): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $sessionId = ($validated['session_id'] ?? null) ?: (string) Str::uuid();

        ChatMessage::create(['session_id' => $sessionId, 'role' => 'user', 'message' => $validated['message']]);

        $reply = $ai->reply($validated['message'], $this->history($sessionId));

        ChatMessage::create(['session_id' => $sessionId, 'role' => 'assistant', 'message' => $reply]);

        return response()->json(['reply' => $reply, 'session_id' => $sessionId]);
    }

    public function stream(Request $request, string $key, AiChatService $ai)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $sessionId = ($validated['session_id'] ?? null) ?: (string) Str::uuid();

        ChatMessage::create(['session_id' => $sessionId, 'role' => 'user', 'message' => $validated['message']]);
        $history = $this->history($sessionId);

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
            } catch (\Throwable) {
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
     * Visitor polls for human-agent replies during a live handoff.
     */
    public function feed(Request $request, string $key): JsonResponse
    {
        $data = $request->validate(['session_id' => 'required|string|max:64']);

        return response()->json([
            'messages' => ChatMessage::where('session_id', $data['session_id'])
                ->oldest()->get(['role', 'message', 'created_at']),
        ]);
    }

    public function handoff(Request $request, string $key): JsonResponse
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
                'Chat session '.substr($data['session_id'], 0, 8).'…', '/chats?session='.$data['session_id']);
        }

        return response()->json(['status' => 'ok']);
    }

    private function history(string $sessionId): array
    {
        return ChatMessage::where('session_id', $sessionId)
            ->latest()->take(10)->get()->reverse()
            ->map(fn (ChatMessage $m) => [
                'role' => $m->role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m->message]],
            ])->values()->all();
    }
}
