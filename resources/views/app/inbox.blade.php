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
                    <a href="mailto:{{ $msg->email }}" class="inline-block mt-3 text-xs font-medium text-indigo-600 hover:text-indigo-800">Reply via email →</a>
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
