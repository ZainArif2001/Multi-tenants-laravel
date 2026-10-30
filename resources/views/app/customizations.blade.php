<x-tenant-app-layout>
    <x-slot name="header">{{ __('Customize Workspace') }}</x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3">{{ session('success') }}</div>
            @endif

            {{-- Custom CSS — instant, per-tenant only --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900">{{ __('Custom CSS') }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ __('Applied only to YOUR workspace — other tenants keep the default design.') }}</p>
                <form method="POST" action="{{ route('tenant.customizations.css') }}" class="mt-4">
                    @csrf
                    <textarea name="custom_css" rows="6" placeholder=".sidebar { background: #1a202c; }&#10;h1 { color: purple; }"
                              class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg font-mono text-xs">{{ old('custom_css', $customCss) }}</textarea>
                    <button class="mt-3 px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700">{{ __('Save CSS') }}</button>
                </form>
            </div>

            {{-- Request bigger changes from super admin --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900">{{ __('Request a change') }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ __('Need layout/structure changes? Describe it — the platform admin will implement it for your tenant only.') }}</p>
                <form method="POST" action="{{ route('tenant.customizations.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-3">
                        <input name="title" required placeholder="{{ __('What do you want changed?') }}"
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                        <select name="module" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                            <option value="">{{ __('Which module? (optional)') }}</option>
                            @foreach (\App\Models\Tenant::MODULES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                            <option value="site">{{ __('Public website') }}</option>
                        </select>
                    </div>
                    <textarea name="details" rows="4" required placeholder="{{ __('Describe exactly what to change — colors, layout, fields, behavior...') }}"
                              class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm"></textarea>
                    <button class="px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700">{{ __('Send request') }}</button>
                </form>
            </div>

            {{-- My requests --}}
            <div class="bg-white border border-gray-200 rounded-2xl divide-y divide-gray-100">
                <div class="px-6 py-4 font-semibold text-gray-900">{{ __('My requests') }}</div>
                @forelse ($requests as $req)
                    <div class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ $req->title }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $req->details }}</div>
                                @if ($req->module)
                                    <span class="text-[10px] font-bold uppercase text-gray-400">{{ $req->module }}</span>
                                @endif
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0
                                {{ ['pending' => 'bg-amber-100 text-amber-700', 'in_progress' => 'bg-blue-100 text-blue-700', 'done' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'][$req->status] ?? 'bg-gray-100' }}">
                                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                            </span>
                        </div>
                        @if ($req->admin_note)
                            <div class="mt-2 text-xs bg-gray-50 rounded-lg px-3 py-2 text-gray-600">
                                <strong>{{ __('Admin') }}:</strong> {{ $req->admin_note }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-gray-400">{{ __('No requests yet.') }}</p>
                @endforelse
            </div>
            {{ $requests->links() }}
        </div>
    </div>
</x-tenant-app-layout>
