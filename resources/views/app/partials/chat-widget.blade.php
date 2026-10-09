<div x-data="chatWidget()" class="fixed bottom-5 right-5 z-50" x-cloak>
    <!-- Chat panel -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-4 w-[92vw] max-w-sm bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col"
         style="height: 480px;">

        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">
                {{ strtoupper(substr(tenant('name'), 0, 1)) }}
            </div>
            <div class="flex-1">
                <div class="text-white font-semibold text-sm">{{ tenant('name') }} Assistant</div>
                <div class="text-indigo-100 text-xs flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Online — replies instantly
                </div>
            </div>
            <button @click="open = false" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Messages -->
        <div x-ref="messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
            <template x-for="(msg, i) in messages" :key="i">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                            ? 'bg-indigo-600 text-white rounded-2xl rounded-br-sm'
                            : 'bg-white text-gray-800 border border-gray-200 rounded-2xl rounded-bl-sm shadow-sm'"
                         class="max-w-[80%] px-4 py-2.5 text-sm leading-relaxed whitespace-pre-line"
                         x-text="msg.text"></div>
                </div>
            </template>

            <!-- Typing indicator -->
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-sm shadow-sm px-4 py-3 flex gap-1.5">
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: .15s"></span>
                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: .3s"></span>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="border-t border-gray-200 p-3 bg-white">
            <form @submit.prevent="send()" class="flex items-center gap-2">
                <input x-model="draft" type="text" maxlength="1000"
                       placeholder="Type your message..."
                       class="flex-1 text-sm border-gray-300 rounded-full px-4 py-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" :disabled="loading || ! draft.trim()"
                        class="w-10 h-10 shrink-0 rounded-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center text-white transition">
                    <svg class="w-4 h-4 -rotate-45 translate-x-px" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Floating button -->
    <button @click="toggle()" aria-label="Chat with us"
            class="ml-auto block w-14 h-14 rounded-full bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 shadow-lg shadow-indigo-300 flex items-center justify-center text-white transition transform hover:scale-105">
        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
</div>

<script>
    const tenantName = {{ Js::from(tenant('name')) }};

    function chatWidget() {
        return {
            open: false,
            draft: '',
            loading: false,
            sessionId: localStorage.getItem('chat_session') || null,
            messages: [],

            toggle() {
                this.open = ! this.open;
                if (this.open && this.messages.length === 0) {
                    this.messages.push({
                        role: 'assistant',
                        text: `Hi there! I'm the ${tenantName} assistant. How can I help you today?`
                    });
                }
                this.$nextTick(() => this.scrollDown());
            },

            async send() {
                const text = this.draft.trim();
                if (! text || this.loading) return;

                this.messages.push({ role: 'user', text });
                this.draft = '';
                this.loading = true;
                this.scrollDown();

                try {
                    const res = await fetch('{{ route('tenant.chat.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ message: text, session_id: this.sessionId }),
                    });
                    const data = await res.json();

                    if (data.session_id) {
                        this.sessionId = data.session_id;
                        localStorage.setItem('chat_session', data.session_id);
                    }

                    this.messages.push({
                        role: 'assistant',
                        text: data.reply ?? 'Sorry, something went wrong. Please try again.'
                    });
                } catch (e) {
                    this.messages.push({ role: 'assistant', text: 'Network error — please check your connection and try again.' });
                }

                this.loading = false;
                this.$nextTick(() => this.scrollDown());
            },

            scrollDown() {
                this.$nextTick(() => {
                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                });
            },
        };
    }
</script>
