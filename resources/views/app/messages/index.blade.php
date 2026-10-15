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

                        <!-- New group -->
                        <button @click="groupOpen = ! groupOpen" class="w-full mt-2 text-xs font-medium text-indigo-600 hover:text-indigo-800 text-left px-1">
                            <span x-text="groupOpen ? '− Cancel group' : '👥 Create a group'"></span>
                        </button>
                        <form x-show="groupOpen" x-cloak method="POST" action="{{ route('tenant.messages.start-group') }}" class="mt-2 space-y-2">
                            @csrf
                            <input name="name" type="text" placeholder="{{ __('Group name (optional)') }}"
                                   class="w-full border-gray-300 rounded-lg text-xs px-3 py-1.5">
                            <div class="max-h-32 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                                @foreach ($contacts as $contact)
                                    <label class="flex items-center gap-2 px-3 py-1.5 text-sm hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" name="user_ids[]" value="{{ $contact->id }}" class="rounded border-gray-300 text-indigo-600">
                                        {{ $contact->name }}
                                    </label>
                                @endforeach
                            </div>
                            <button class="w-full bg-indigo-600 text-white text-xs font-semibold py-2 rounded-lg hover:bg-indigo-700">{{ __('Create group') }}</button>
                        </form>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <template x-if="! convos.length">
                            <p class="p-6 text-sm text-gray-400 text-center">{{ __('No conversations yet. Pick a teammate above.') }}</p>
                        </template>
                        <template x-for="c in convos" :key="c.id">
                            <button @click="open(c.id)"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition border-b border-gray-100"
                                    :class="{ 'bg-indigo-50': activeId === c.id }">
                                <span class="relative shrink-0">
                                    <template x-if="c.avatar">
                                        <img :src="c.avatar" class="w-10 h-10 rounded-full object-cover" alt="">
                                    </template>
                                    <template x-if="! c.avatar">
                                        <span class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold" x-text="c.initial"></span>
                                    </template>
                                    <span x-show="isOnline(c.other_id)" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                                </span>
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
                <section class="flex-1 flex flex-col min-w-0 bg-[#efeae2] relative" :class="{ 'hidden md:flex': ! activeId }">
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
                                    <div class="text-xs" :class="typing || isOnline(activeConvo()?.other_id) ? 'text-green-600' : 'text-gray-400'"
                                         x-text="typing ? '{{ __('typing...') }}' : (isOnline(activeConvo()?.other_id) ? '{{ __('Online') }}' : '{{ __('Offline') }}')"></div>
                                </div>
                                <div class="ml-auto flex items-center gap-2">
                                    <button x-show="! call.active && ! call.outgoing" @click="startCall()" title="{{ __('Voice call') }}"
                                            class="w-9 h-9 rounded-full bg-green-100 text-green-600 hover:bg-green-200 flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.4 21 3 13.6 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.24.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                                    </button>
                                    <button x-show="call.active || call.outgoing" @click="hangUp()" title="{{ __('End call') }}"
                                            class="w-9 h-9 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition">
                                        <svg class="w-4 h-4 rotate-[135deg]" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.4 21 3 13.6 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.24.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Call overlay --}}
                            <div x-show="call.outgoing || call.incoming" x-cloak
                                 class="absolute inset-x-0 top-16 mx-auto w-64 bg-slate-900 text-white rounded-2xl shadow-2xl p-4 text-center z-40">
                                <div x-show="call.incoming">
                                    <p class="text-sm font-semibold">{{ __('Incoming call') }}</p>
                                    <p class="text-xs text-slate-300 mt-1" x-text="activeConvo()?.name"></p>
                                    <div class="flex justify-center gap-3 mt-3">
                                        <button @click="acceptCall()" class="px-4 py-1.5 bg-green-500 rounded-full text-xs font-bold">{{ __('Accept') }}</button>
                                        <button @click="declineCall()" class="px-4 py-1.5 bg-red-500 rounded-full text-xs font-bold">{{ __('Decline') }}</button>
                                    </div>
                                </div>
                                <div x-show="call.outgoing && ! call.active">
                                    <p class="text-sm font-semibold">{{ __('Calling...') }}</p>
                                    <div class="flex justify-center gap-1 mt-2">
                                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce"></span>
                                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:.15s"></span>
                                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:.3s"></span>
                                    </div>
                                </div>
                            </div>
                            <div x-show="call.active" x-cloak class="absolute top-16 right-4 bg-green-600 text-white text-xs font-bold px-3 py-1.5 rounded-full z-40">
                                🔊 {{ __('On call') }}
                            </div>
                            <audio x-ref="remoteAudio" autoplay class="hidden"></audio>

                            <!-- Messages -->
                            <div x-ref="thread" class="flex-1 overflow-y-auto px-5 py-4 space-y-2">
                                <template x-for="m in messages" :key="m.id">
                                    <div class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[70%] px-3 py-2 rounded-xl shadow-sm text-sm"
                                             :class="m.mine ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none'">
                                            <template x-if="m.attachment_url">
                                                <div class="mb-1">
                                                    <template x-if="m.is_image">
                                                        <a :href="m.attachment_url" target="_blank"><img :src="m.attachment_url" class="rounded-lg max-w-[220px] max-h-48 object-cover" alt=""></a>
                                                    </template>
                                                    <template x-if="! m.is_image">
                                                        <a :href="m.attachment_url" target="_blank"
                                                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium"
                                                           :class="m.mine ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-700'">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                            <span x-text="m.attachment_name || 'File'"></span>
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>
                                            <p x-show="m.body" class="whitespace-pre-wrap break-words" x-text="m.body"></p>
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
                                <input type="file" x-ref="fileInput" class="hidden" @change="file = $event.target.files[0]">
                                <button type="button" @click="$refs.fileInput.click()" class="text-gray-400 hover:text-indigo-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </button>
                                <span x-show="file" class="text-xs text-gray-500 max-w-[120px] truncate" x-text="file?.name"></span>
                                <input x-model="draft" @input="whisperTyping()" type="text" placeholder="{{ __('Type a message...') }}"
                                       class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-full text-sm px-4">
                                <button type="submit" :disabled="! draft.trim() && ! file"
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
                file: null,
                lastId: 0,
                poll: null,
                channel: null,
                onlineIds: [],
                typing: false,
                groupOpen: false,
                pc: null,
                call: { active: false, incoming: false, outgoing: false, stream: null, pendingOffer: null },
                typingTimer: null,
                lastWhisper: 0,

                init() {
                    if (this._started) return; // guard against double init
                    this._started = true;
                    if (this.activeId) {
                        this.loadFeed();
                        this.subscribeConvo(this.activeId);
                    }
                    this.subscribeMe();
                    this.joinPresence();
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

                            if (e.conversation_id === this.activeId) {
                                if (! this.messages.some(m => m.id === e.id)) {
                                    this.messages.push({
                                        id: e.id, sender_id: e.sender_id, sender_name: e.sender_name,
                                        body: e.body, time: e.time, mine: false, status: 'received',
                                        attachment_url: e.attachment_url, attachment_name: e.attachment_name, is_image: e.is_image,
                                    });
                                    this.lastId = Math.max(this.lastId, e.id);
                                    this.scrollBottom();
                                    if (document.visibilityState === 'visible') this.markRead();
                                }
                            } else {
                                this.toast(e.sender_name + ': ' + e.body);
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
                    this.typing = false;
                    this.loadFeed();
                    this.subscribeConvo(id);
                },

                // Who is online right now (presence channel)
                joinPresence() {
                    if (! window.Echo) return;
                    window.Echo.join('presence-team')
                        .here(users => { this.onlineIds = users.map(u => u.id); })
                        .joining(u => { if (! this.onlineIds.includes(u.id)) this.onlineIds.push(u.id); })
                        .leaving(u => { this.onlineIds = this.onlineIds.filter(i => i !== u.id); });
                },

                isOnline(userId) {
                    return this.onlineIds.includes(userId);
                },

                // Per-conversation channel — typing whispers + call signaling
                subscribeConvo(id) {
                    if (! window.Echo) return;
                    if (this.channel) window.Echo.leave(`conversation.${this.channel}`);
                    this.channel = id;
                    const ch = window.Echo.private(`conversation.${id}`);
                    ch.listenForWhisper('typing', (e) => {
                        this.typing = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.typing = false, 3000);
                    });
                    // --- WebRTC signaling via whispers ---
                    ch.listenForWhisper('call-offer', async (e) => {
                        this.call.incoming = true;
                        this.call.pendingOffer = e.sdp;
                    });
                    ch.listenForWhisper('call-answer', async (e) => {
                        await this.pc?.setRemoteDescription(e.sdp);
                        this.call.active = true;
                    });
                    ch.listenForWhisper('call-ice', async (e) => {
                        try { await this.pc?.addIceCandidate(e.candidate); } catch (err) {}
                    });
                    ch.listenForWhisper('call-end', () => this.hangUp(false));
                },

                whisper(evt, data = {}) {
                    if (this.channel) window.Echo?.private(`conversation.${this.channel}`).whisper(evt, data);
                },

                whisperTyping() {
                    const now = Date.now();
                    if (now - this.lastWhisper < 1500) return;
                    this.lastWhisper = now;
                    this.whisper('typing');
                },

                // ---------- 1:1 voice call (WebRTC over whisper signaling) ----------
                async makePeer() {
                    this.pc = new RTCPeerConnection({ iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] });
                    this.call.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.call.stream.getTracks().forEach(t => this.pc.addTrack(t, this.call.stream));
                    this.pc.onicecandidate = e => { if (e.candidate) this.whisper('call-ice', { candidate: e.candidate }); };
                    this.pc.ontrack = e => { this.$refs.remoteAudio.srcObject = e.streams[0]; };
                },

                async startCall() {
                    this.call.outgoing = true;
                    await this.makePeer();
                    const offer = await this.pc.createOffer();
                    await this.pc.setLocalDescription(offer);
                    this.whisper('call-offer', { sdp: offer });
                },

                async acceptCall() {
                    this.call.incoming = false;
                    await this.makePeer();
                    await this.pc.setRemoteDescription(this.call.pendingOffer);
                    const answer = await this.pc.createAnswer();
                    await this.pc.setLocalDescription(answer);
                    this.whisper('call-answer', { sdp: answer });
                    this.call.active = true;
                },

                declineCall() {
                    this.call.incoming = false;
                    this.whisper('call-end');
                },

                hangUp(notify = true) {
                    if (notify) this.whisper('call-end');
                    this.pc?.close();
                    this.call.stream?.getTracks().forEach(t => t.stop());
                    this.pc = null;
                    this.call = { active: false, incoming: false, outgoing: false, pc: null, stream: null, pendingOffer: null };
                    if (this.$refs.remoteAudio) this.$refs.remoteAudio.srcObject = null;
                },

                toast(msg) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: msg }));
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
                    if (! body && ! this.file) return;
                    this.draft = '';

                    const fd = new FormData();
                    fd.append('body', body);
                    if (this.file) fd.append('attachment', this.file);
                    this.file = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';

                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/send`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                        body: fd,
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
