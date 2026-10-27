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
use App\Events\ConversationEvent;
use App\Events\MessageSent;
use App\Models\MessageReaction;

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

        $projects = \App\Models\Project::orderBy('name')->pluck('name', 'id');

        return view('app.messages.index', compact('conversations', 'contacts', 'openId', 'projects'));
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
     * Create a group conversation with selected members.
     */
    public function startGroup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:60',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $conversation = DB::transaction(function () use ($request, $data) {
            $c = Conversation::create(['type' => 'group']);
            $c->name = $data['name'] ?? null;
            $c->save();
            $c->users()->attach(
                collect($data['user_ids'])->push($request->user()->id)->unique()->all(),
                ['joined_at' => now()]
            );

            return $c;
        });

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
            ->with('receipts', 'reactions', 'replyTo.sender')
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

        $data = $request->validate([
            'body' => 'nullable|string|max:10000',
            'attachment' => 'nullable|file|max:10240',
            'reply_to_id' => 'nullable|integer|exists:messages,id',
        ]);
        abort_if(empty($data['body']) && ! $request->hasFile('attachment'), 422);

        // Per-plan message limit — starter plans hit a daily cap
        $limits = ['starter' => 500, 'business' => 2000, 'agency' => 10000];
        $cap = $limits[tenant('plan') ?? 'custom'] ?? 10000;
        abort_unless(
            Message::where('sender_id', $request->user()->id)->whereDate('created_at', today())->count() < $cap,
            429, 'Daily message limit reached for your plan.'
        );

        $message = DB::transaction(function () use ($request, $conversation, $data) {
            $path = $name = null;
            if ($file = $request->file('attachment')) {
                $name = $file->getClientOriginalName();
                $path = $file->store('chat', 'public');
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'body' => $data['body'] ?? '',
                'attachment_path' => $path,
                'attachment_name' => $name,
                'reply_to_id' => $data['reply_to_id'] ?? null,
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

            return $message->load('receipts', 'sender', 'reactions', 'replyTo.sender');
        });

        // Push to every recipient's personal channel via Reverb
        foreach ($message->conversation->users as $recipient) {
            if ($recipient->id !== $request->user()->id) {
                \App\Models\UserNotification::notify($recipient->id, 'message',
                    "New message from {$request->user()->name}",
                    \Illuminate\Support\Str::limit($data['body'] ?? '📎 file', 60),
                    route('tenant.messages.index', ['c' => $message->conversation_id]));
                MessageSent::dispatch($message, $recipient->id);
            }
        }

        // "@ai <question>" → assistant replies in-thread, tenant-scoped context
        if (str_starts_with(trim($data['body'] ?? ''), '@ai ')) {
            $this->dispatchAiReply($conversation, trim(substr($data['body'], 3)));
        }

        return response()->json([
            'message' => $this->messageData($message, $request->user()),
        ], 201);
    }

    /**
     * Edit own message → edited_at + broadcast.
     */
    public function update(Request $request, Message $message): JsonResponse
    {
        $this->authorizeMessageOwner($request, $message);
        $data = $request->validate(['body' => 'required|string|max:10000']);

        $message->update(['body' => $data['body'], 'edited_at' => now()]);
        ConversationEvent::dispatch($message->conversation_id, 'updated', [
            'message_id' => $message->id,
            'body' => $message->body,
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Soft-delete own message → broadcast removal.
     */
    public function destroy(Request $request, Message $message): JsonResponse
    {
        $this->authorizeMessageOwner($request, $message);
        $message->delete();
        ConversationEvent::dispatch($message->conversation_id, 'deleted', [
            'message_id' => $message->id,
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Toggle an emoji reaction on a message.
     */
    public function react(Request $request, Message $message): JsonResponse
    {
        $data = $request->validate(['emoji' => 'required|string|max:10']);

        abort_unless(
            $message->conversation->users()->where('users.id', $request->user()->id)->exists(),
            404
        );

        $existing = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $request->user()->id)
            ->where('emoji', $data['emoji'])->first();

        $existing ? $existing->delete() : MessageReaction::create([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'emoji' => $data['emoji'],
        ]);

        $counts = $message->fresh()->reactions->groupBy('emoji')->map->count();
        ConversationEvent::dispatch($message->conversation_id, 'reaction', [
            'message_id' => $message->id,
            'reactions' => $counts,
        ]);

        return response()->json(['reactions' => $counts]);
    }

    /**
     * Search messages inside one conversation.
     */
    public function search(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeMember($request, $conversation);
        $q = trim($request->query('q', ''));
        abort_if(strlen($q) < 2, 422);

        $hits = $conversation->messages()
            ->where('body', 'like', "%{$q}%")
            ->latest('id')->take(20)->get()
            ->map(fn ($m) => $this->messageData($m, $request->user()));

        return response()->json(['results' => $hits]);
    }

    /**
     * Pin / archive a conversation for the current user only.
     */
    public function preference(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeMember($request, $conversation);
        $data = $request->validate([
            'pinned' => 'nullable|boolean',
            'archived' => 'nullable|boolean',
        ]);

        $pivot = [];
        if (array_key_exists('pinned', $data)) {
            $pivot['pinned_at'] = $data['pinned'] ? now() : null;
        }
        if (array_key_exists('archived', $data)) {
            $pivot['archived_at'] = $data['archived'] ? now() : null;
        }

        $conversation->users()->updateExistingPivot($request->user()->id, $pivot);

        return response()->json(['status' => 'ok']);
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

    /**
     * Convert a chat message into a project task.
     */
    public function makeTask(Request $request, Message $message): JsonResponse
    {
        abort_unless(
            $message->conversation->users()->where('users.id', $request->user()->id)->exists(),
            404
        );
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
        ]);

        $task = \App\Models\Task::create([
            'project_id' => $data['project_id'],
            'assigned_to' => $request->user()->id,
            'title' => $data['title'],
            'description' => "From chat: " . \Illuminate\Support\Str::limit($message->body, 200),
            'status' => 'todo',
        ]);

        return response()->json(['task_id' => $task->id], 201);
    }

    /**
     * Export a conversation as CSV.
     */
    public function export(Request $request, Conversation $conversation)
    {
        $this->authorizeMember($request, $conversation);

        $rows = $conversation->messages()->with('sender')->oldest()->get()
            ->map(fn ($m) => sprintf(
                '"%s","%s","%s","%s"',
                $m->created_at->toIso8601String(),
                addslashes($m->sender->name ?? '?'),
                $m->is_ai ? 'AI' : '',
                addslashes(str_replace('"', '""', $m->body . ($m->attachment_name ? " [file:{$m->attachment_name}]" : '')))
            ))->implode("\n");

        return response("time,sender,ai,message\n{$rows}", 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=chat-{$conversation->id}.csv",
        ]);
    }

    /**
     * The AI assistant replies in-thread using this tenant's own data only.
     */
    protected function dispatchAiReply(Conversation $conversation, string $question): void
    {
        try {
            $bot = User::firstOrCreate(
                ['email' => 'assistant@ai.local'],
                ['name' => 'AI Assistant', 'password' => bcrypt(str()->random(24)), 'allowed_modules' => ['messaging']]
            );
            if (! $conversation->users()->where('users.id', $bot->id)->exists()) {
                $conversation->users()->attach($bot->id, ['joined_at' => now()]);
            }

            $answer = app(\App\Services\AiChatService::class)->reply($question);

            $reply = DB::transaction(function () use ($conversation, $bot, $answer, $question) {
                $m = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $bot->id,
                    'body' => $answer,
                    'is_ai' => true,
                ]);
                foreach ($conversation->users()->where('users.id', '!=', $bot->id)->pluck('users.id') as $uid) {
                    MessageReceipt::create(['message_id' => $m->id, 'user_id' => $uid]);
                }
                $conversation->touch();

                return $m->load('sender');
            });

            foreach ($conversation->users as $member) {
                if ($member->id !== $bot->id) {
                    MessageSent::dispatch($reply, $member->id);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AI reply failed: ' . $e->getMessage());
        }
    }

    protected function authorizeMessageOwner(Request $request, Message $message): void
    {
        abort_unless($message->sender_id === $request->user()->id, 403);
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
        $isGroup = $c->type === 'group';
        $name = $isGroup
            ? ($c->name ?: $c->users->where('id', '!=', $me->id)->pluck('name')->take(3)->implode(', '))
            : ($other?->name ?? 'Conversation #'.$c->id);

        return [
            'id' => $c->id,
            'other_id' => $isGroup ? null : $other?->id,
            'is_group' => $isGroup,
            'name' => $name,
            'avatar' => $isGroup ? null : $other?->avatar_url,
            'initial' => $isGroup ? 'G' : strtoupper(substr($other?->name ?? 'C', 0, 1)),
            'last_body' => $last?->body ? \Illuminate\Support\Str::limit($last->body, 40) : ($last?->attachment_name ? '📎 '.$last->attachment_name : null),
            'last_at' => $last?->created_at?->toIso8601String(),
            'last_time' => $last?->created_at?->format('H:i'),
            'unread' => $c->unreadCountFor($me),
            'pinned' => (bool) $c->users->firstWhere('id', $me->id)?->pivot?->pinned_at,
            'archived' => (bool) $c->users->firstWhere('id', $me->id)?->pivot?->archived_at,
        ];
    }

    protected function messageData(Message $m, User $me): array
    {
        return [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'sender_name' => $m->sender->name ?? null,
            'body' => $m->body,
            'edited' => $m->edited_at !== null,
            'is_ai' => (bool) $m->is_ai,
            'reply_to' => $m->reply_to_id ? [
                'id' => $m->reply_to_id,
                'body' => \Illuminate\Support\Str::limit($m->replyTo->body ?: ($m->replyTo->attachment_name ? '📎 file' : ''), 60),
                'sender_name' => $m->replyTo->sender?->name,
            ] : null,
            'reactions' => $m->reactions->groupBy('emoji')->map->count(),
            'attachment_url' => $m->attachment_url,
            'attachment_name' => $m->attachment_name,
            'is_audio' => $m->attachment_name && preg_match('/\.(webm|mp3|ogg|m4a|wav)$/i', $m->attachment_name),
            'is_image' => $m->is_image,
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
