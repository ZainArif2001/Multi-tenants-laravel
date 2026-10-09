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
                                <a href="{{ route('tenant.chats.index', ['session' => $s->session_id]) }}"
                                   class="block px-4 py-3 hover:bg-gray-50 {{ $session === $s->session_id ? 'bg-indigo-50' : '' }}">
                                    <div class="text-xs font-mono text-gray-500 truncate">{{ Str::limit($s->session_id, 20) }}</div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $s->total }} {{ __('messages') }} · {{ \Carbon\Carbon::parse($s->last_at)->diffForHumans() }}
                                    </div>
                                </a>
                            @empty
                                <p class="p-4 text-sm text-gray-500">{{ __('No conversations yet.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Messages pane -->
                    <div class="flex-1 p-6 overflow-y-auto" style="max-height: 480px;">
                        @if ($messages->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($messages as $msg)
                                    <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-[70%] px-4 py-2.5 text-sm rounded-2xl leading-relaxed whitespace-pre-line
                                            {{ $msg->role === 'user'
                                                ? 'bg-indigo-600 text-white rounded-br-sm'
                                                : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
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
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
