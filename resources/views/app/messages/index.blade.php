<x-tenant-app-layout>
    <x-slot name="header">{{ __('Messages') }}</x-slot>

    <div class="py-6" x-data="teamChat({
            openId: {{ $openId }},
            convos: {{ Js::from($conversations) }},
            projects: {{ Js::from($projects) }},
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
                            <button @click="showArchived = ! showArchived" class="text-xs text-indigo-600 hover:text-indigo-800"
                                    x-text="showArchived ? '{{ __('Active') }}' : '{{ __('Archived') }}'"></button>
                        </div>
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
                        <template x-if="! visibleConvos().length">
                            <p class="p-6 text-sm text-gray-400 text-center">{{ __('No conversations yet. Pick a teammate above.') }}</p>
                        </template>
                        <template x-for="c in visibleConvos()" :key="c.id">
                            <div class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-100 group relative"
                                 :class="{ 'bg-indigo-50': activeId === c.id }">
                                <button @click="open(c.id)" class="flex items-center gap-3 flex-1 min-w-0 text-left">
                                    <span class="relative shrink-0">
                                        <template x-if="c.avatar">
                                            <img :src="c.avatar" class="w-10 h-10 rounded-full object-cover" alt="">
                                        </template>
                                        <template x-if="! c.avatar">
                                            <span class="w-10 h-10 rounded-full flex items-center justify-center font-bold"
                                                  :class="c.is_group ? 'bg-emerald-600 text-white' : 'bg-indigo-600 text-white'" x-text="c.initial"></span>
                                        </template>
                                        <span x-show="isOnline(c.other_id)" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="flex justify-between items-center">
                                            <span class="text-sm font-semibold text-gray-900 truncate">
                                                <span x-show="c.is_group" class="text-[10px] mr-1">👥</span><span x-text="c.name"></span>
                                            </span>
                                            <span class="text-[11px] text-gray-400 shrink-0" x-text="c.last_time || ''"></span>
                                        </span>
                                        <span class="flex justify-between items-center mt-0.5">
                                            <span class="text-xs text-gray-500 truncate" x-text="c.last_body || '{{ __('Say hello 👋') }}'"></span>
                                            <span class="flex items-center gap-1 shrink-0">
                                                <span x-show="c.pinned" class="text-[10px]">📌</span>
                                                <span x-show="c.unread > 0" class="bg-indigo-600 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center" x-text="c.unread"></span>
                                            </span>
                                        </span>
                                    </span>
                                </button>
                                <!-- hover actions: pin / archive -->
                                <span class="hidden group-hover:flex flex-col gap-0.5 absolute right-2 top-1">
                                    <button @click.stop="pref(c, 'pinned', ! c.pinned)" class="text-[10px] px-1 rounded bg-white border border-gray-200 shadow-sm" title="Pin">📌</button>
                                    <button @click.stop="pref(c, 'archived', ! c.archived)" class="text-[10px] px-1 rounded bg-white border border-gray-200 shadow-sm" title="Archive">📥</button>
                                </span>
                            </div>
                        </template>
                    </div>
                </aside>

                <!-- Thread -->
                <section class="flex-1 flex flex-col min-w-0 bg-[#efeae2] relative" :class="{ 'hidden md:flex': ! activeId }"
                         @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                         @drop.prevent="onDrop($event)">
                    <template x-if="! activeId">
                        <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                            {{ __('Select a conversation or start a new one') }}
                        </div>
                    </template>

                    <template x-if="activeId">
                        <div class="flex flex-col flex-1 min-h-0">
                            <!-- Thread header -->
                            <div class="bg-white border-b border-gray-200 px-5 py-3 flex items-center gap-3 relative">
                                <button @click="activeId = null" class="md:hidden text-gray-500 mr-1">←</button>
                                <span class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold" x-text="activeConvo()?.initial"></span>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm" x-text="activeConvo()?.name"></div>
                                    <div class="text-xs" :class="typing || isOnline(activeConvo()?.other_id) ? 'text-green-600' : 'text-gray-400'"
                                         x-text="typing ? '{{ __('typing...') }}' : (activeConvo()?.is_group ? '{{ __('Group chat') }}' : (isOnline(activeConvo()?.other_id) ? '{{ __('Online') }}' : '{{ __('Offline') }}'))"></div>
                                </div>
                                <div class="ml-auto flex items-center gap-2">
                                    <button @click="searchOpen = ! searchOpen; searchQ = ''; searchResults = []" title="{{ __('Search in chat') }}"
                                            class="w-9 h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </button>
                                    <button x-show="! call.active && ! call.outgoing && ! activeConvo()?.is_group" @click="startCall()" title="{{ __('Voice call') }}"
                                            class="w-9 h-9 rounded-full bg-green-100 text-green-600 hover:bg-green-200 flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.4 21 3 13.6 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.24.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                                    </button>
                                    <button x-show="call.active" @click="toggleVideo()" title="{{ __('Toggle video') }}"
                                        class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                <a :href="`{{ url('/messages') }}/${activeId}/export`" title="{{ __('Export chat') }}"
                                   class="w-9 h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                                <button x-show="call.active || call.outgoing" @click="hangUp()" title="{{ __('End call') }}"
                                            class="w-9 h-9 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition">
                                        <svg class="w-4 h-4 rotate-[135deg]" fill="currentColor" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1C10.4 21 3 13.6 3 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.24.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                                    </button>
                                </div>
                                <!-- search popover -->
                                <div x-show="searchOpen" x-cloak class="absolute right-3 top-14 w-72 bg-white rounded-xl shadow-xl border border-gray-200 z-40 p-3">
                                    <input x-model="searchQ" @input.debounce.300ms="doSearch()" placeholder="{{ __('Search messages...') }}"
                                           class="w-full border-gray-300 rounded-lg text-sm px-3 py-1.5">
                                    <div class="mt-2 max-h-56 overflow-y-auto divide-y divide-gray-100">
                                        <template x-for="r in searchResults" :key="r.id">
                                            <button @click="jumpTo(r.id)" class="w-full text-left px-2 py-2 hover:bg-gray-50 text-xs">
                                                <span class="font-medium text-gray-800" x-text="r.sender_name"></span>
                                                <span class="block text-gray-500 truncate" x-text="r.body"></span>
                                            </button>
                                        </template>
                                    </div>
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
                            {{-- video call overlay --}}
                            <div x-show="call.video" x-cloak class="absolute inset-x-4 top-20 bottom-24 bg-black rounded-2xl z-40 overflow-hidden">
                                <video x-ref="remoteVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                                <video x-ref="localVideo" autoplay playsinline muted class="absolute bottom-3 right-3 w-32 h-24 object-cover rounded-lg border-2 border-white/30"></video>
                            </div>

                            {{-- drag-drop overlay --}}
                            <div x-show="dragging" x-cloak class="absolute inset-0 bg-indigo-600/20 border-4 border-dashed border-indigo-500 z-50 flex items-center justify-center pointer-events-none">
                                <span class="bg-white text-indigo-700 font-semibold px-6 py-3 rounded-2xl shadow">{{ __('Drop files to send') }}</span>
                            </div>

                            <!-- Messages -->
                            <div x-ref="thread" class="flex-1 overflow-y-auto px-5 py-4 space-y-2">
                                <template x-for="m in messages" :key="m.id">
                                    <div>
                                        <div x-show="m.id === unreadStart" class="flex items-center gap-2 my-3">
                                            <span class="flex-1 border-t border-indigo-300"></span>
                                            <span class="text-[10px] font-bold text-indigo-500 uppercase">{{ __('New messages') }}</span>
                                            <span class="flex-1 border-t border-indigo-300"></span>
                                        </div>
                                        <div class="flex group" :class="m.mine ? 'justify-end' : 'justify-start'" :id="'msg-' + m.id">
                                            <div class="relative max-w-[70%]">
                                                {{-- hover action bar --}}
                                                <div class="hidden group-hover:flex absolute -top-4 items-center gap-1 bg-white border border-gray-200 rounded-full shadow px-1.5 py-0.5 z-30"
                                                     :class="m.mine ? 'left-0' : 'right-0'">
                                                    <template x-for="e in ['👍','❤️','😂']" :key="e">
                                                        <button @click="react(m, e)" class="text-xs hover:scale-125 transition" x-text="e"></button>
                                                    </template>
                                                    <button @click="replyTo = { id: m.id, body: m.body || '📎 file', sender_name: m.sender_name }" class="text-[10px] text-gray-400 hover:text-indigo-600 px-1" title="Reply">↩</button>
                                                    <button x-show="m.mine && m.body" @click="editMessage(m)" class="text-[10px] text-gray-400 hover:text-indigo-600 px-1" title="Edit">✏️</button>
                                                    <button x-show="m.mine" @click="deleteMessage(m)" class="text-[10px] text-gray-400 hover:text-red-600 px-1" title="Delete">🗑</button>
                                                    <button @click="forwarding = m" class="text-[10px] text-gray-400 hover:text-indigo-600 px-1" title="Forward">📤</button>
                                                    <button x-show="m.body" @click="tasking = m" class="text-[10px] text-gray-400 hover:text-emerald-600 px-1" title="Create task">✅</button>
                                                </div>
                                                <div class="px-3 py-2 rounded-xl shadow-sm text-sm"
                                                     :class="[m.mine ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none', highlighted === m.id ? 'ring-2 ring-yellow-400' : '']">
                                                    {{-- quoted reply --}}
                                                    <div x-show="m.reply_to" class="mb-1 px-2 py-1 rounded border-l-4 text-xs"
                                                         :class="m.mine ? 'border-indigo-300 bg-indigo-500/60 text-indigo-100' : 'border-indigo-400 bg-indigo-50 text-gray-500'">
                                                        <div class="font-semibold" x-text="m.reply_to?.sender_name"></div>
                                                        <div class="truncate" x-text="m.reply_to?.body"></div>
                                                    </div>
                                                    <template x-if="m.is_ai">
                                                        <div class="text-[9px] font-bold uppercase tracking-wide mb-0.5" :class="m.mine ? 'text-indigo-200' : 'text-purple-500'">✨ AI Assistant</div>
                                                    </template>
                                                    <template x-if="m.attachment_url">
                                                        <div class="mb-1">
                                                            <template x-if="m.is_image">
                                                                <a :href="m.attachment_url" target="_blank"><img :src="m.attachment_url" class="rounded-lg max-w-[220px] max-h-48 object-cover" alt=""></a>
                                                            </template>
                                                            <template x-if="m.is_audio">
                                                                <audio controls :src="m.attachment_url" class="max-w-[220px] h-9"></audio>
                                                            </template>
                                                            <template x-if="! m.is_image && ! m.is_audio">
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
                                                        <span x-show="m.edited" class="text-[9px] italic" :class="m.mine ? 'text-indigo-200' : 'text-gray-400'">edited</span>
                                                        <span class="text-[10px]" :class="m.mine ? 'text-indigo-200' : 'text-gray-400'" x-text="m.time"></span>
                                                        <template x-if="m.mine">
                                                            <span class="text-[11px] leading-none"
                                                                  :class="m.status === 'read' ? 'text-cyan-300' : 'text-indigo-200'"
                                                                  x-text="m.status === 'sent' ? '✓' : '✓✓'"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                                {{-- reactions chips --}}
                                                <div x-show="m.reactions && Object.keys(m.reactions).length"
                                                     class="flex gap-1 mt-0.5 flex-wrap" :class="m.mine ? 'justify-end' : 'justify-start'">
                                                    <template x-for="(count, emoji) in m.reactions" :key="emoji">
                                                        <button @click="react(m, emoji)"
                                                                class="text-[11px] bg-white border border-gray-200 rounded-full px-1.5 py-0.5 shadow-sm hover:border-indigo-400">
                                                            <span x-text="emoji"></span><span class="text-gray-500" x-text="count"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="! messages.length">
                                    <p class="text-center text-xs text-gray-400 py-8">{{ __('No messages yet — say hello!') }}</p>
                                </template>
                            </div>

                            {{-- reply preview --}}
                            <div x-show="replyTo" x-cloak class="bg-gray-50 border-t border-gray-200 px-4 py-2 flex items-center gap-3">
                                <span class="w-1 self-stretch bg-indigo-500 rounded"></span>
                                <div class="flex-1 min-w-0 text-xs">
                                    <div class="font-semibold text-indigo-600" x-text="replyTo?.sender_name"></div>
                                    <div class="text-gray-500 truncate" x-text="replyTo?.body"></div>
                                </div>
                                <button @click="replyTo = null" class="text-gray-400 hover:text-gray-600">✕</button>
                            </div>

                            <!-- Input -->
                            <form @submit.prevent="send()" class="bg-white border-t border-gray-200 px-4 py-3 flex items-center gap-3">
                                <input type="file" x-ref="fileInput" multiple class="hidden" @change="queueFiles($event.target.files); $event.target.value = ''">
                                <button type="button" @click="$refs.fileInput.click()" class="text-gray-400 hover:text-indigo-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </button>
                                <button type="button" @click="toggleRecord()" :class="recording ? 'text-red-500 animate-pulse' : 'text-gray-400 hover:text-indigo-600'"
                                        class="transition" title="{{ __('Voice note') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                </button>
                                <span x-show="files.length" class="text-xs text-gray-500 max-w-[120px] truncate" x-text="files.map(f=>f.name).join(', ')"></span>
                                <input x-model="draft" @input="whisperTyping()" type="text" placeholder="{{ __('Type a message... (@ai to ask AI)') }}"
                                       class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-full text-sm px-4">
                                <button type="submit" :disabled="! draft.trim() && ! files.length"
                                        class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 disabled:opacity-40 transition">
                                    <svg class="w-4 h-4 rotate-90" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                </button>
                            </form>
                        </div>
                    </template>
                </section>
            </div>
        </div>

        {{-- Forward modal --}}
        <div x-show="forwarding" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center" @click.self="forwarding = null">
            <div class="bg-white rounded-2xl shadow-2xl w-80 p-5">
                <h3 class="font-semibold text-gray-900">{{ __('Forward to...') }}</h3>
                <div class="mt-3 max-h-64 overflow-y-auto divide-y divide-gray-100">
                    <template x-for="c in convos.filter(x => x.id !== activeId)" :key="c.id">
                        <button @click="forwardTo(c.id)" class="w-full flex items-center gap-3 px-2 py-2.5 hover:bg-gray-50 rounded-lg text-left">
                            <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold" x-text="c.initial"></span>
                            <span class="text-sm text-gray-800 truncate" x-text="c.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Create-task modal --}}
        <div x-show="tasking" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center" @click.self="tasking = null">
            <div class="bg-white rounded-2xl shadow-2xl w-96 p-5">
                <h3 class="font-semibold text-gray-900">{{ __('Create task from message') }}</h3>
                <p class="text-xs text-gray-400 mt-1 truncate" x-text="tasking?.body"></p>
                <input x-ref="taskTitle" :value="tasking?.body?.substring(0, 80)" placeholder="{{ __('Task title') }}"
                       class="mt-3 w-full border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select x-model="taskProject" class="mt-2 w-full border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option :value="null">{{ __('Select project...') }}</option>
                    <template x-for="(name, id) in projects" :key="id">
                        <option :value="id" x-text="name"></option>
                    </template>
                </select>
                <button @click="createTask()" :disabled="! taskProject"
                        class="mt-3 w-full py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 disabled:opacity-40">
                    {{ __('Create task') }}
                </button>
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
                files: [],
                lastId: 0,
                poll: null,
                channel: null,
                onlineIds: [],
                typing: false,
                typingTimer: null,
                lastWhisper: 0,
                groupOpen: false,
                showArchived: false,
                searchOpen: false,
                searchQ: '',
                searchResults: [],
                replyTo: null,
                highlighted: null,
                unreadStart: null,
                dragging: false,
                unreadTotal: 0,
                baseTitle: document.title,
                pc: null,
                call: { active: false, incoming: false, outgoing: false, stream: null, pendingOffer: null, video: false },
                forwarding: null,
                tasking: null,
                projects: cfg.projects || {},
                taskProject: null,
                recording: null,
                recChunks: [],

                init() {
                    if (this._started) return;
                    this._started = true;
                    if (this.activeId) {
                        this.loadFeed();
                        this.subscribeConvo(this.activeId);
                    }
                    this.subscribeMe();
                    this.joinPresence();
                    this.poll = setInterval(() => {
                        if (this.activeId) this.fetchNew();
                        this.refreshList();
                    }, 5000);
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible' && this.activeId) this.markRead();
                    });
                },

                visibleConvos() {
                    return this.convos
                        .filter(c => this.showArchived ? c.archived : ! c.archived)
                        .sort((a, b) => (b.pinned - a.pinned) || ((b.last_at || '') < (a.last_at || '') ? -1 : 1));
                },

                activeConvo() {
                    return this.convos.find(c => c.id === this.activeId);
                },

                open(id) {
                    this.activeId = id;
                    this.messages = [];
                    this.lastId = 0;
                    this.typing = false;
                    this.replyTo = null;
                    this.unreadStart = null;
                    this.loadFeed();
                    this.subscribeConvo(id);
                },

                joinPresence() {
                    if (! window.Echo) return;
                    window.Echo.join('presence-team')
                        .here(users => { this.onlineIds = users.map(u => u.id); })
                        .joining(u => { if (! this.onlineIds.includes(u.id)) this.onlineIds.push(u.id); })
                        .leaving(u => { this.onlineIds = this.onlineIds.filter(i => i !== u.id); });
                },

                isOnline(userId) {
                    return userId && this.onlineIds.includes(userId);
                },

                subscribeMe() {
                    if (! window.Echo) return;
                    window.Echo.private(`user.{{ auth()->id() }}`)
                        .listen('.message.sent', (e) => {
                            this.ackDelivered(e.id);
                            if (e.conversation_id === this.activeId) {
                                if (! this.messages.some(m => m.id === e.id)) {
                                    this.messages.push(this.eventMessage(e));
                                    this.lastId = Math.max(this.lastId, e.id);
                                    this.scrollBottom();
                                    if (document.visibilityState === 'visible') this.markRead();
                                }
                            } else {
                                this.toast(e.sender_name + ': ' + (e.body || '📎 file'));
                            }
                            this.refreshList();
                        });
                },

                eventMessage(e) {
                    return {
                        id: e.id, sender_id: e.sender_id, sender_name: e.sender_name,
                        body: e.body, time: e.time, mine: false, status: 'received',
                        attachment_url: e.attachment_url, attachment_name: e.attachment_name, is_image: e.is_image,
                        reactions: {}, reply_to: null, edited: false, is_ai: e.is_ai || false,
                    };
                },

                subscribeConvo(id) {
                    if (! window.Echo) return;
                    if (this.channel) window.Echo.leave(`conversation.${this.channel}`);
                    this.channel = id;
                    const ch = window.Echo.private(`conversation.${id}`);
                    ch.listenForWhisper('typing', () => {
                        this.typing = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.typing = false, 3000);
                    });
                    // live edits / deletes / reactions
                    ch.listen('.conversation.event', (e) => {
                        if (e.type === 'updated') {
                            this.messages = this.messages.map(m => m.id === e.message_id ? { ...m, body: e.body, edited: true } : m);
                        } else if (e.type === 'deleted') {
                            this.messages = this.messages.filter(m => m.id !== e.message_id);
                        } else if (e.type === 'reaction') {
                            this.messages = this.messages.map(m => m.id === e.message_id ? { ...m, reactions: e.reactions } : m);
                        }
                    });
                    // WebRTC signaling
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

                // ---------- WebRTC voice call ----------
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

                // ---------- data ----------
                async loadFeed() {
                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/feed`);
                    const data = await res.json();
                    this.messages = data.messages;
                    this.lastId = this.messages.length ? this.messages[this.messages.length - 1].id : 0;
                    const c = this.activeConvo();
                    if (c && c.unread > 0) {
                        // divider before first unread incoming message
                        const idx = this.messages.findIndex(m => ! m.mine && this.messages.indexOf(m) >= this.messages.length - c.unread);
                        this.unreadStart = idx >= 0 ? this.messages[idx].id : null;
                    }
                    this.scrollBottom();
                    if (document.visibilityState === 'visible') this.markRead();
                    this.updateTitle(data.unread_total);
                },

                async fetchNew() {
                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/feed?after_id=${this.lastId}`);
                    const data = await res.json();
                    const fresh = data.messages.filter(m => ! this.messages.some(x => x.id === m.id));
                    if (fresh.length) {
                        this.messages.push(...fresh);
                        this.lastId = data.messages[data.messages.length - 1].id;
                        this.scrollBottom();
                        if (document.visibilityState === 'visible') this.markRead();
                    }
                    if (data.statuses) {
                        this.messages = this.messages.map(m => data.statuses[m.id] ? { ...m, status: data.statuses[m.id] } : m);
                    }
                    this.updateTitle(data.unread_total);
                },

                async send() {
                    const body = this.draft.trim();
                    if (! body && ! this.files.length) return;
                    this.draft = '';

                    const fd = new FormData();
                    fd.append('body', body);
                    if (this.replyTo) fd.append('reply_to_id', this.replyTo.id);
                    const firstFile = this.files.shift();
                    if (firstFile) fd.append('attachment', firstFile); // first file rides with the caption

                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/send`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                        body: fd,
                    });
                    const data = await res.json();
                    this.messages.push(data.message);
                    this.lastId = data.message.id;
                    this.replyTo = null;
                    this.scrollBottom();

                    // extra queued files send one-by-one
                    for (const f of this.files) {
                        const ffd = new FormData();
                        ffd.append('body', '');
                        ffd.append('attachment', f);
                        const r2 = await fetch(`${cfg.urls.feed}/${this.activeId}/send`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                            body: ffd,
                        });
                        const d2 = await r2.json();
                        this.messages.push(d2.message);
                        this.lastId = d2.message.id;
                    }
                    this.files = [];
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
                    this.updateTitle(data.unread_total);
                },

                updateTitle(n) {
                    if (typeof n === 'number') document.title = n > 0 ? `(${n}) ${this.baseTitle}` : this.baseTitle;
                },

                // ---------- message actions ----------
                async react(m, emoji) {
                    const res = await fetch(`${cfg.urls.feed}/${m.id}/react`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ emoji }),
                    });
                    const data = await res.json();
                    m.reactions = data.reactions;
                },

                async editMessage(m) {
                    const body = prompt('{{ __('Edit message') }}', m.body);
                    if (! body || body === m.body) return;
                    await fetch(`${cfg.urls.feed}/${m.id}`, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ body }),
                    });
                    m.body = body;
                    m.edited = true;
                },

                async deleteMessage(m) {
                    if (! confirm('{{ __('Delete this message?') }}')) return;
                    await fetch(`${cfg.urls.feed}/${m.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf },
                    });
                    this.messages = this.messages.filter(x => x.id !== m.id);
                },

                async forwardTo(targetId) {
                    const m = this.forwarding;
                    this.forwarding = null;
                    if (! m) return;
                    const fd = new FormData();
                    fd.append('body', `↪ ${m.body || '📎 ' + (m.attachment_name || 'file')}`);
                    await fetch(`${cfg.urls.feed}/${targetId}/send`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf },
                        body: fd,
                    });
                    this.toast('{{ __('Forwarded!') }}');
                    this.refreshList();
                },

                async createTask() {
                    const res = await fetch(`${cfg.urls.feed}/${this.tasking.id}/task`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ title: this.$refs.taskTitle.value, project_id: this.taskProject }),
                    });
                    this.tasking = null;
                    this.taskProject = null;
                    this.toast(res.ok ? '{{ __('Task created ✓') }}' : '{{ __('Failed to create task') }}');
                },

                async toggleRecord() {
                    if (this.recording) {
                        this.recording.stop();
                        return;
                    }
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.recChunks = [];
                    this.recording = new MediaRecorder(stream);
                    this.recording.ondataavailable = e => this.recChunks.push(e.data);
                    this.recording.onstop = async () => {
                        const blob = new Blob(this.recChunks, { type: 'audio/webm' });
                        const fd = new FormData();
                        fd.append('body', '');
                        fd.append('attachment', blob, `voice-${Date.now()}.webm`);
                        const res = await fetch(`${cfg.urls.feed}/${this.activeId}/send`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': cfg.csrf },
                            body: fd,
                        });
                        const data = await res.json();
                        this.messages.push(data.message);
                        this.lastId = data.message.id;
                        this.scrollBottom();
                        stream.getTracks().forEach(t => t.stop());
                        this.recording = null;
                    };
                    this.recording.start();
                    this.toast('🎤 {{ __('Recording… click mic again to send') }}');
                },

                async toggleVideo() {
                    if (! this.pc) return;
                    this.call.video = ! this.call.video;
                    if (this.call.video) {
                        const vs = await navigator.mediaDevices.getUserMedia({ video: true });
                        vs.getVideoTracks().forEach(t => { this.pc.addTrack(t, vs); (this.call.vstream = vs); });
                        this.$nextTick(() => {
                            this.$refs.localVideo.srcObject = vs;
                            this.$refs.remoteVideo.srcObject = new MediaStream(
                                this.pc.getReceivers().filter(r => r.track?.kind === 'video').map(r => r.track)
                            );
                        });
                    } else {
                        this.call.vstream?.getTracks().forEach(t => t.stop());
                    }
                },

                async pref(c, key, value) {
                    const fd = new FormData();
                    fd.append(key, value ? '1' : '0');
                    await fetch(`${cfg.urls.feed}/${c.id}/preference`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf },
                        body: fd,
                    });
                    c[key] = value;
                },

                async doSearch() {
                    if (this.searchQ.trim().length < 2) { this.searchResults = []; return; }
                    const res = await fetch(`${cfg.urls.feed}/${this.activeId}/search?q=${encodeURIComponent(this.searchQ)}`);
                    const data = await res.json();
                    this.searchResults = data.results;
                },

                jumpTo(id) {
                    this.searchOpen = false;
                    const el = document.getElementById('msg-' + id);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        this.highlighted = id;
                        setTimeout(() => this.highlighted = null, 2000);
                    }
                },

                // ---------- files ----------
                queueFiles(list) {
                    this.files.push(...list);
                },

                onDrop(e) {
                    this.dragging = false;
                    if (e.dataTransfer.files.length) this.queueFiles(e.dataTransfer.files);
                },

                async ackDelivered(messageId) {
                    await fetch(`${cfg.urls.feed}/${messageId}/delivered`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf },
                    });
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

                toast(msg) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: msg }));
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
