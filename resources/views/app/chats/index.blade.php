<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex divide-x divide-gray-200" style="min-height: 480px;">

                    <!-- Sessions list -->
                    <div class="w-72 shrink-0">
                        <div class="p-4 border-b border-gray-200 font-semibold text-sm text-gray-700">
                            {{ __('Conversations') }}
                        </div>
                        <div class="divide-y divide-gray-100 overflow-y-auto" style="max-height: 480px;">
                            @forelse ($sessions as $s)
                                <div class="flex items-center px-4 py-3 hover:bg-gray-50 {{ $session === $s->session_id ? 'bg-indigo-50' : '' }}">
                                    <a href="{{ route('tenant.chats.index', ['session' => $s->session_id]) }}" class="flex-1 min-w-0">
                                        <div class="text-xs font-mono text-gray-500 truncate">
                                            {{ Str::limit($s->session_id, 20) }}
                                            @if ($s->handoff)
                                                <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] font-bold">🙋 HUMAN</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            {{ $s->total }} {{ __('messages') }} · {{ \Carbon\Carbon::parse($s->last_at)->diffForHumans() }}
                                        </div>
                                    </a>
                                    @if (auth()->user()->hasRole('admin'))
                                        <form method="POST" action="{{ route('tenant.chats.destroy', $s->session_id) }}"
                                              onsubmit="return confirm('{{ __('Delete this conversation?') }}')">
                                            @csrf @method('DELETE')
                                            <button class="text-gray-300 hover:text-red-500 px-1" title="{{ __('Delete') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="p-4 text-sm text-gray-500">{{ __('No conversations yet.') }}</p>
                            @endforelse
                        </div>
                        <div class="p-3 border-t border-gray-100">
                            {{ $sessions->links() }}
                        </div>
                    </div>

                    <!-- Messages pane + agent reply -->
                    <div class="flex-1 flex flex-col min-h-0">
                        <div class="flex-1 p-6 overflow-y-auto" style="max-height: 430px;" x-data
                             x-init="$el.scrollTop = $el.scrollHeight">
                            @if ($messages->isNotEmpty())
                                <div class="space-y-3">
                                @foreach ($messages as $msg)
                                    <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-[70%] px-4 py-2.5 text-sm rounded-2xl leading-relaxed whitespace-pre-line
                                            {{ $msg->role === 'user'
                                                ? 'bg-indigo-600 text-white rounded-br-sm'
                                                : ($msg->role === 'staff' ? 'bg-green-100 text-green-900 border border-green-200 rounded-bl-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm') }}">
                                            @if ($msg->role === 'staff')
                                                <span class="block text-[9px] font-bold text-green-700 uppercase">{{ __('Agent') }}</span>
                                            @endif
                                            {{ $msg->message }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="h-full flex items-center justify-center text-sm text-gray-400">
                                {{ __('Select a conversation to view messages.') }}
                            </div>
                        @endif
                        </div>
                        @if ($session)
                            <form x-data="{ draft: '', sending: false }"
                                  @submit.prevent="async () => {
                                      if (! draft.trim() || sending) return;
                                      sending = true;
                                      await fetch('{{ route('tenant.chats.reply') }}', {
                                          method: 'POST',
                                          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                          body: JSON.stringify({ session_id: @js($session), message: draft }),
                                      });
                                      draft = ''; sending = false; location.reload();
                                  }"
                                  class="border-t border-gray-200 px-4 py-3 flex gap-2 bg-white">
                                <input x-model="draft" type="text" placeholder="{{ __('Reply as agent...') }}"
                                       class="flex-1 border-gray-300 rounded-full text-sm px-4 focus:border-indigo-500 focus:ring-indigo-500">
                                <button class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-full hover:bg-indigo-700">
                                    {{ __('Send') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
