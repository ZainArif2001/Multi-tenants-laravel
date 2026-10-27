<x-app-guest-layout>
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Two-factor authentication') }}</h2>
        <p class="mt-2 text-sm text-gray-500">{{ __('Enter the 6-digit code from your authenticator app, or a recovery code.') }}</p>
    </div>

    <form method="POST" action="{{ route('tenant.2fa.verify') }}" class="mt-6">
        @csrf
        <x-text-input id="code" class="block mt-1 w-full text-center text-2xl tracking-[0.5em]" type="text"
                      name="code" required autofocus autocomplete="one-time-code" placeholder="000000" />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />

        <x-primary-button class="mt-4 w-full justify-center">{{ __('Verify') }}</x-primary-button>
    </form>
</x-app-guest-layout>
