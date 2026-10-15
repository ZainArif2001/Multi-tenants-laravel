<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Events\MessageSent;

class TeamChatController extends Controller
{
    /**
     * Chat page — conversation list + open thread (if ?c= given).
     */
    public function index(Request $request): View
    {
        $me = $request->user();

        $conversations = $me->conversations()
            ->with(['users', 'latestMessage'])
            ->latest('updated_at')
            ->get()
            ->map(fn ($c) => $this->convoData($c, $me))
            ->sortByDesc(fn ($c) => $c['last_at'] ?? '')
            ->values();

        $contacts = User::where('id', '!=', $me->id)->orderBy('name')->get(['id', 'name']);

        $openId = (int) $request->query('c', $conversations->first()['id'] ?? 0);

        return view('app.messages.index', compact('conversations', 'contacts', 'openId'));
    }

    /**
     * Start (or reopen) a private chat with another tenant user.
     */
    public function start(Request $request): RedirectResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $me = $request->user();
        $otherId = (int) $request->input('user_id');
        abort_if($otherId === $me->id, 422);

        $conversation = Conversation::where('type', 'private')
            ->whereHas('users', fn ($q) => $q->where('users.id', $me->id))
            ->whereHas('users', fn ($q) => $q->where('users.id', $otherId))
            ->first();

        if (! $conversation) {
            $conversation = DB::transaction(function () use ($me, $otherId) {
                $c = Conversation::create(['type' => 'private']);
                $c->users()->attach([$me->id, $otherId], ['joined_at' => now()]);

                return $c;
            });
        }

        return redirect()->route('tenant.messages.index', ['c' => $conversation->id]);
    }

    /**
     * Poll endpoint — returns messages (optionally after a given id) and
     * marks fetched incoming messages as delivered for the current user.
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeMember($request, $conversation);

        $after = (int) $request->query('after_id', 0);

        $messages = $conversation->messages()
            ->with('receipts')
            ->when($after, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->get();

        // Fetching = delivered for the recipient
        MessageReceipt::where('user_id', $request->user()->id)
            ->whereNull('delivered_at')
            ->whereIn('message_id', $messages->pluck('id'))
            ->update(['delivered_at' => now()]);

        // Re-query statuses after delivery updates
        $messages->load('receipts');

        // Receipt statuses for my recent messages — lets polling update ✓✓ ticks
        $statuses = $conversation->messages()
            ->where('sender_id', $request->user()->id)
            ->latest('id')->take(100)->get()
            ->mapWithKeys(fn ($m) => [$m->id => $m->statusFor($request->user())]);

        return response()->json([
            'messages' => $messages->map(fn ($m) => $this->messageData($m, $request->user())),
            'statuses' => $statuses,
            'unread_total' => $this->unreadTotal($request->user()),
        ]);
    }

    /**
     * Send a message — save + create a receipt row per recipient.
     */
    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeMember($request, $conversation);

        $data = $request->validate(['body' => 'required|string|max:10000']);

        $message = DB::transaction(function () use ($request, $conversation, $data) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'body' => $data['body'],
            ]);

            $recipients = $conversation->users()
                ->where('users.id', '!=', $request->user()->id)
                ->pluck('users.id');

            foreach ($recipients as $userId) {
                MessageReceipt::create([
                    'message_id' => $message->id,
                    'user_id' => $userId,
                ]);
            }

            $conversation->touch(); // bump for list ordering

            return $message->load('receipts', 'sender');
        });

        // Push to every recipient's personal channel via Reverb
        foreach ($message->conversation->users as $recipient) {
            if ($recipient->id !== $request->user()->id) {
                MessageSent::dispatch($message, $recipient->id);
            }
        }

        return response()->json([
            'message' => $this->messageData($message, $request->user()),
        ], 201);
    }

    /**
     * Delivery acknowledgement — client received the pushed message.
     */
    public function delivered(Request $request, Message $message): JsonResponse
    {
        $userId = $request->user()->id;

        abort_unless(
            $message->conversation->users()->where('users.id', $userId)->exists(),
            404
        );
        abort_if($message->sender_id === $userId, 403);

        MessageReceipt::where('message_id', $message->id)
            ->where('user_id', $userId)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        return response()->json(['status' => 'delivered']);
    }

    /**
     * Mark everything incoming in this conversation as read.
     */
    public function read(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeMember($request, $conversation);

        MessageReceipt::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->whereHas('message', fn ($q) => $q->where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $request->user()->id))
            ->update(['read_at' => now(), 'delivered_at' => now()]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Sidebar list refresh — conversation cards with unread badges.
     */
    public function conversations(Request $request): JsonResponse
    {
        $me = $request->user();

        $list = $me->conversations()
            ->with(['users', 'latestMessage'])
            ->get()
            ->map(fn ($c) => $this->convoData($c, $me))
            ->sortByDesc(fn ($c) => $c['last_at'] ?? '')
            ->values();

        return response()->json([
            'conversations' => $list,
            'unread_total' => $this->unreadTotal($me),
        ]);
    }

    protected function authorizeMember(Request $request, Conversation $conversation): void
    {
        abort_unless(
            $conversation->users()->where('users.id', $request->user()->id)->exists(),
            404
        );
    }

    protected function convoData(Conversation $c, User $me): array
    {
        $other = $c->users->firstWhere('id', '!=', $me->id);
        $last = $c->latestMessage;

        return [
            'id' => $c->id,
            'name' => $other?->name ?? 'Conversation #'.$c->id,
            'initial' => strtoupper(substr($other?->name ?? 'C', 0, 1)),
            'last_body' => $last?->body ? \Illuminate\Support\Str::limit($last->body, 40) : null,
            'last_at' => $last?->created_at?->toIso8601String(),
            'last_time' => $last?->created_at?->format('H:i'),
            'unread' => $c->unreadCountFor($me),
        ];
    }

    protected function messageData(Message $m, User $me): array
    {
        return [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'sender_name' => $m->sender->name ?? null,
            'body' => $m->body,
            'time' => $m->created_at->format('H:i'),
            'mine' => $m->sender_id === $me->id,
            'status' => $m->statusFor($me),
        ];
    }

    protected function unreadTotal(User $me): int
    {
        return MessageReceipt::where('user_id', $me->id)->whereNull('read_at')->count();
    }
}
