<x-tenant-app-layout>
    <x-slot name="header">{{ __('Webhooks') }}</x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3">{{ session('success') }}</div>
            @endif

            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900">{{ __('Add a webhook') }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ __('We\'ll POST JSON to your URL when these events happen. Secret signs the request via X-Webhook-Signature.') }}</p>
                <form method="POST" action="{{ route('tenant.webhooks.store') }}" class="mt-4 grid sm:grid-cols-2 gap-3">
                    @csrf
                    <input name="name" required placeholder="{{ __('Name (e.g. Zapier)') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                    <input name="url" required type="url" placeholder="https://hooks.example.com/..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                    <input name="secret" placeholder="{{ __('Secret (optional)') }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                    <div class="flex items-center gap-4">
                        @foreach ($events as $key => $label)
                            <label class="flex items-center gap-1.5 text-xs text-gray-600">
                                <input type="checkbox" name="events[]" value="{{ $key }}" class="rounded border-gray-300 text-indigo-600"> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <div class="sm:col-span-2">
                        <button class="px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700 transition">{{ __('Add webhook') }}</button>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl divide-y divide-gray-100">
                @forelse ($hooks as $hook)
                    <div class="flex items-center gap-4 px-6 py-4">
                        <span class="w-2 h-2 rounded-full {{ $hook->active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900">{{ $hook->name }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $hook->url }}</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ implode(', ', $hook->events) }}</div>
                        </div>
                        <form method="POST" action="{{ route('tenant.webhooks.toggle', $hook) }}">@csrf
                            <button class="text-xs text-indigo-600 hover:text-indigo-800">{{ $hook->active ? __('Pause') : __('Resume') }}</button>
                        </form>
                        <form method="POST" action="{{ route('tenant.webhooks.destroy', $hook) }}" onsubmit="return confirm('{{ __('Remove this webhook?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                        </form>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-gray-400">{{ __('No webhooks configured.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-tenant-app-layout>
