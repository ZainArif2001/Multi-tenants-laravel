<x-dynamic-component :component="tenant() ? 'tenant-app-layout' : 'app-layout'">
    <x-slot name="header">{{ __('Verify Email') }}</x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
                <svg class="w-12 h-12 text-indigo-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <h2 class="mt-4 font-semibold text-gray-900">{{ __('Verify your email address') }}</h2>
                <p class="mt-2 text-sm text-gray-500">{{ __('We sent a verification link to') }} <strong>{{ auth()->user()->email }}</strong>.</p>

                @if (session('status') === 'verification-link-sent')
                    <div class="mt-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
                        {{ __('A fresh verification link has been sent!') }}
                    </div>
                @endif

                <form method="POST" action="{{ tenant() ? route('tenant.verification.send') : route('verification.send') }}" class="mt-6">
                    @csrf
                    <button class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition">
                        {{ __('Resend verification email') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-dynamic-component>
