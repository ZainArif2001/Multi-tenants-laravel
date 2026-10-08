<x-tenant-guest-layout>
    <div class="text-center py-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ tenant('name') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('Welcome to your workspace.') }}</p>

        <div class="mt-6">
            <x-button-link href="{{ route('tenant.login') }}">
                {{ __('Log in') }}
            </x-button-link>
        </div>
    </div>
</x-tenant-guest-layout>
