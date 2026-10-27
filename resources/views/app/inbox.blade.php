<x-tenant-app-layout>
    <x-slot name="header">{{ __('Contact Inbox') }}</x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($messages as $msg)
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $msg->name }}
                                <span class="font-normal text-gray-400 text-sm">&lt;{{ $msg->email }}&gt;</span>
                            </div>
                            @if ($msg->subject)
                                <div class="text-sm font-medium text-indigo-600 mt-0.5">{{ $msg->subject }}</div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 whitespace-nowrap">{{ $msg->created_at->diffForHumans() }}</div>
                    </div>
                    <p class="text-sm text-gray-600 mt-3 whitespace-pre-wrap">{{ $msg->message }}</p>
                    <div class="flex items-center gap-4 mt-3">
                        <a href="mailto:{{ $msg->email }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Reply via email →</a>
                        <button x-data="{ draft: null, loading: false }"
                                @click="loading = true; const r = await fetch('{{ route('tenant.inbox.suggest', $msg) }}'); draft = (await r.json()).draft; loading = false"
                                class="text-xs font-medium text-purple-600 hover:text-purple-800">
                            <span x-show="! loading">✨ {{ __('Suggest reply') }}</span>
                            <span x-show="loading">{{ __('Drafting...') }}</span>
                            <template x-if="draft">
                                <div @click.stop class="mt-2 p-3 bg-purple-50 border border-purple-200 rounded-lg text-left">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[10px] font-bold text-purple-500 uppercase">{{ __('AI draft') }}</span>
                                        <button @click="navigator.clipboard.writeText(draft); draft = null" class="text-[10px] text-purple-600 underline">{{ __('copy') }}</button>
                                    </div>
                                    <p class="text-xs text-gray-700 mt-1 whitespace-pre-wrap" x-text="draft"></p>
                                </div>
                            </template>
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-400">
                    {{ __('No enquiries yet. Messages from your public contact form will appear here.') }}
                </div>
            @endforelse
            {{ $messages->links() }}
        </div>
    </div>
</x-tenant-app-layout>
