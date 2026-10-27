<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Two-Factor Authentication') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Add an extra layer of security with an authenticator app (Google Authenticator, Authy...).') }}</p>
    </header>

    <div class="mt-6 space-y-4">
        @if (! auth()->user()->google2fa_secret && ! session('pending_2fa_secret') && ! session()->has('pending_2fa_secret'))
            <form method="POST" action="{{ route('tenant.2fa.enable') }}">
                @csrf
                <x-primary-button>{{ __('Enable 2FA') }}</x-primary-button>
            </form>
        @endif

        @if (! auth()->user()->google2fa_secret && session('pending_2fa_secret'))
            <div class="border border-gray-200 rounded-xl p-5 space-y-3">
                <p class="text-sm text-gray-600">{{ __('Scan this QR code with your authenticator app:') }}</p>
                <img src="{{ \App\Http\Controllers\App\TwoFactorController::qrDataUri(session('pending_2fa_secret')) }}"
                     class="w-40 h-40 mx-auto" alt="QR code">
                <p class="text-xs text-center text-gray-400 font-mono">{{ session('pending_2fa_secret') }}</p>
                <form method="POST" action="{{ route('tenant.2fa.confirm') }}" class="flex gap-2">
                    @csrf
                    <x-text-input name="code" placeholder="{{ __('Enter 6-digit code') }}" required class="flex-1" />
                    <x-primary-button>{{ __('Confirm') }}</x-primary-button>
                </form>
            </div>
        @endif

        @if (session('recovery_codes'))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-amber-800">{{ __('Save these recovery codes — shown once!') }}</p>
                <div class="mt-2 grid grid-cols-2 gap-1 font-mono text-sm">
                    @foreach (session('recovery_codes') as $code)
                        <code class="bg-white rounded px-2 py-1 border">{{ $code }}</code>
                    @endforeach
                </div>
            </div>
        @endif

        @if (auth()->user()->google2fa_secret)
            <div class="flex items-center gap-3">
                <span class="text-sm text-green-600 font-medium">✓ {{ __('2FA is enabled') }}</span>
                <form method="POST" action="{{ route('tenant.2fa.disable') }}" class="flex gap-2 items-center">
                    @csrf @method('DELETE')
                    <x-text-input type="password" name="password" placeholder="{{ __('Current password') }}" required />
                    <x-danger-button>{{ __('Disable') }}</x-danger-button>
                </form>
            </div>
        @endif
    </div>
</section>
