<x-tenant-app-layout>
    <x-slot name="header">{{ __('Messages') }}</x-slot>

    <div class="py-6" x-data="teamChat({
            openId: {{ $openId }},
            convos: {{ Js::from($conversations) }},
            urls: {
                start: '{{ route('tenant.messages.start') }}',
                list: '{{ route('tenant.messages.conversations') }}',
                feed: '{{ url('/messages') }}',
            },
            csrf: '{{ csrf_token() }}'
        })">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden flex" style="height: calc(100vh - 180px); min-height: 480px;">

                <!-- Sidebar: conversations -->
                <aside class="w-80 border-r border-gray-200 flex flex-col shrink-0" :class="{ 'hidden md:flex': activeId }">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-gray-900">{{ __('Chats') }}</h3>
                        </div>
                        <!-- New chat picker -->
                        <select @change="startChat($event.target.value); $event.target.value = ''"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                            <option value="">{{ __('+ Start a new chat...') }}</option>
                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <template x-if="! convos.length">
                            <p class="p-6 text-sm text-gray-400 text-center">{{ __('No conversations yet. Pick a teammate above.') }}</p>
                        </template>
                        <template x-for="c in convos" :key="c.id">
                            <button @click="open(c.id)"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition border-b border-gray-100"
                                    :class="{ 'bg-indigo-50': activeId === c.id }">
                                <span class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shrink-0" x-text="c.initial"></span>
                                <span class="flex-1 min-w-0">
                                    <span class="flex justify-between items-center">
                                        <span class="text-sm font-semibold text-gray-900 truncate" x-text="c.name"></span>
                                        <span class="text-[11px] text-gray-400 shrink-0" x-text="c.last_time || ''"></span>
                                    </span>
                                    <span class="flex justify-between items-center mt-0.5">
                                        <span class="text-xs text-gray-500 truncate" x-text="c.last_body || '{{ __('Say hello 👋') }}'"></span>
                                        <span x-show="c.unread > 0" class="ml-2 bg-indigo-600 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center shrink-0" x-text="c.unread"></span>
                                    </span>
                                </span>
                            </button>
                        </template>
                    </div>
                </aside>

                <!-- Thread -->
                <section class="flex-1 flex flex-col min-w-0 bg-[#efeae2]" :class="{ 'hidden md:flex': ! activeId }">
                    <template x-if="! activeId">
                        <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                            {{ __('Select a conversation or start a new one') }}
                        </div>
                    </template>

                    <template x-if="activeId">
                        <div class="flex flex-col flex-1 min-h-0">
                            <!-- Thread header -->
                            <div class="bg-white border-b border-gray-200 px-5 py-3 flex items-center gap-3">
                                <button @click="activeId = null" class="md:hidden text-gray-500 mr-1">←</button>
                                <span class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold" x-text="activeConvo()?.initial"></span>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm" x-text="activeConvo()?.name"></div>
                                    <div class="text-xs text-green-600">{{ __('Online') }}</div>
                                </div>
                            </div>

                            <!-- Messages -->
                            <div x-ref="thread" class="flex-1 overflow-y-auto px-5 py-4 space-y-2">
                                <template x-for="m in messages" :key="m.id">
                                    <div class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[70%] px-3 py-2 rounded-xl shadow-sm text-sm"
                                             :class="m.mine ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none'">
                                            <p class="whitespace-pre-wrap break-words" x-text="m.body"></p>
                                            <div class="flex items-center justify-end gap-1 mt-1">
                                                <span class="text-[10px]" :class="m.mine ? 'text-indigo-200' : 'text-gray-400'" x-text="m.time"></span>
                                                <template x-if="m.mine">
                                                    <span class="text-[11px] leading-none"
                                                          :class="m.status === 'read' ? 'text-cyan-300' : 'text-indigo-200'"
                                                          x-text="m.status === 'sent' ? '✓' : '✓✓'"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="! messages.length">
                                    <p class="text-center text-xs text-gray-400 py-8">{{ __('No messages yet — say hello!') }}</p>
                                </template>
                            </div>

                            <!-- Input -->
                            <form @submit.prevent="send()" class="bg-white border-t border-gray-200 px-4 py-3 flex items-center gap-3">
                                <input x-model="draft" type="text" placeholder="{{ __('Type a message...') }}"
                                       class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-full text-sm px-4">
                                <button type="submit" :disabled="! draft.trim()"
                                        class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 disabled:opacity-40 transition">
                                    <svg class="w-4 h-4 rotate-90" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                </button>
                            </form>
                        </div>
                    </template>
                </section>
            </div>
        </div>
    </div>

    <script>
        function teamChat(cfg) {
            return {
                activeId: cfg.openId || null,
                convos: cfg.convos || [],
                messages: [],
                draft: '',
                lastId: 0,
                poll: null,
                channel: null,

                init() {
                    if (this._started) return; // guard against double init
                    this._started = true;
                    if (this.activeId) this.loadFeed();
                    this.subscribeMe();
                    // Safety-net poll — covers any missed websocket event
                    this.poll = setInterval(() => {
                        if (this.activeId) this.fetchNew();
                        this.refreshList();
                    }, 5000);
                    // Coming back to the tab = actually looking at the chat → mark read
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible' && this.activeId) this.markRead();
                    });
                },

                // My personal channel — I get EVERY message for me here,
                // regardless of which conversation is open.
                subscribeMe() {
                    if (! window.Echo) return;
                    window.Echo.private(`user.{{ auth()->id() }}`)
                        .listen('.message.sent', (e) => {
                            // device received it = delivered (WhatsApp semantics)
                            this.ackDelivered(e.id);

                            if (e.conversation_id === this.activeId
                                && ! this.messages.some(m => m.id === e.id)) {
                                this.messages.push({
                                    id: e.id, sender_id: e.sender_id, sender_name: e.sender_name,
                                    body: e.body, time: e.time, mine: false, status: 'received',
                                });
                                this.lastId = Math.max(this.lastId, e.id);
                                this.scrollBottom();
                                if (document.visibilityState === 'visible') this.markRead();
                            }
                            this.refreshList();
                        });
                },

                async ackDelivered(messageId) {
                    await fetch(`${cfg.urls.feed}/${messageId}/delivered`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf },
                    });
                },

                activeConvo() {
                    return this.convos.find(c => c.id === this.activeId);
                },

                open(id) {
                    this.activeId = id;
                    this.messages = [];
                    this.lastId = 0;
                    this.loadFeed();
                },

                async loadFeed() {
                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/feed`);
                    const data = await res.json();
                    this.messages = data.messages;
                    this.lastId = this.messages.length ? this.messages[this.messages.length - 1].id : 0;
                    this.scrollBottom();
                    if (document.visibilityState === 'visible') this.markRead();
                },

                async fetchNew() {
                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/feed?after_id=${this.lastId}`);
                    const data = await res.json();

                    // dedupe — an echo push may have already added these
                    const fresh = data.messages.filter(m => ! this.messages.some(x => x.id === m.id));
                    if (fresh.length) {
                        this.messages.push(...fresh);
                        this.lastId = data.messages[data.messages.length - 1].id;
                        this.scrollBottom();
                        // "Read" only if the user is actually looking — tab visible
                        if (document.visibilityState === 'visible') this.markRead();
                    }
                    // refresh tick statuses on my messages (delivered/read)
                    if (data.statuses) {
                        this.messages = this.messages.map(m => data.statuses[m.id] ? { ...m, status: data.statuses[m.id] } : m);
                    }
                },

                async send() {
                    const body = this.draft.trim();
                    if (! body) return;
                    this.draft = '';

                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/send`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ body }),
                    });
                    const data = await res.json();
                    this.messages.push(data.message);
                    this.lastId = data.message.id;
                    this.scrollBottom();
                },

                async markRead() {
                    await fetch(`${cfg.urls.feed}/${this.activeId}/read`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf },
                    });
                    const c = this.activeConvo();
                    if (c) c.unread = 0;
                },

                async refreshList() {
                    const res = await fetch(cfg.urls.list);
                    const data = await res.json();
                    this.convos = data.conversations;
                },

                startChat(userId) {
                    if (! userId) return;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = cfg.urls.start;
                    form.innerHTML = `<input type="hidden" name="_token" value="${cfg.csrf}"><input type="hidden" name="user_id" value="${userId}">`;
                    document.body.appendChild(form);
                    form.submit();
                },

                scrollBottom() {
                    this.$nextTick(() => {
                        if (this.$refs.thread) this.$refs.thread.scrollTop = this.$refs.thread.scrollHeight;
                    });
                },
            };
        }
    </script>
</x-tenant-app-layout>
