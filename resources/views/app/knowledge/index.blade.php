<x-tenant-app-layout>
    <x-slot name="header">{{ __('AI Knowledge Base') }}</x-slot>
    <x-slot name="actions">
        <span class="text-xs text-gray-400">Feeds the visitor AI chatbot</span>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3">{{ session('success') }}</div>
            @endif

            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900">{{ __('Upload a document') }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ __('PDF, DOCX, TXT, MD or CSV — the AI will answer questions using its content.') }}</p>
                <form method="POST" action="{{ route('tenant.knowledge.store') }}" enctype="multipart/form-data" class="mt-4 flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <input name="title" required placeholder="{{ __('Document title (e.g. Pricing FAQ)') }}"
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                    </div>
                    <input type="file" name="file" required accept=".pdf,.docx,.txt,.md,.csv" class="text-sm text-gray-600">
                    <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700 transition">
                        {{ __('Upload') }}
                    </button>
                </form>
                <x-input-error :messages="$errors->get('file')" class="mt-2" />
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl divide-y divide-gray-100">
                @forelse ($docs as $doc)
                    <div class="flex items-center gap-4 px-6 py-4">
                        <span class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">{{ $doc->title }}</div>
                            <div class="text-xs text-gray-400">{{ number_format(strlen($doc->content)) }} {{ __('characters extracted') }} · {{ $doc->created_at->diffForHumans() }}</div>
                        </div>
                        <form method="POST" action="{{ route('tenant.knowledge.destroy', $doc) }}" onsubmit="return confirm('{{ __('Remove this document?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-medium text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                        </form>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-gray-400">{{ __('No documents yet — upload one to teach the AI about your business.') }}</p>
                @endforelse
            </div>
            {{ $docs->links() }}
        </div>
    </div>
</x-tenant-app-layout>
