<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('API Tokens') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Personal access tokens for the tenant API (/api/v1/...).') }}</p>
    </header>

    <form method="POST" action="{{ route('tenant.tokens.store') }}" class="mt-6 flex gap-2">
        @csrf
        <x-text-input name="name" placeholder="{{ __('Token name (e.g. Zapier)') }}" required class="flex-1" />
        <x-primary-button>{{ __('Create') }}</x-primary-button>
    </form>

    @if (session('token'))
        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-xs font-bold text-amber-700 mb-1">{{ __('Copy now — shown once:') }}</p>
            <code class="text-xs break-all select-all">{{ session('token') }}</code>
        </div>
    @endif

    <div class="mt-4 divide-y divide-gray-100">
        @foreach (auth()->user()->tokens as $token)
            <div class="flex items-center justify-between py-2 text-sm">
                <span class="text-gray-700">{{ $token->name }} <span class="text-gray-400 text-xs">· {{ $token->created_at->diffForHumans() }}</span></span>
                <form method="POST" action="{{ route('tenant.tokens.destroy', $token->id) }}" onsubmit="return confirm('Revoke this token?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:text-red-800">{{ __('Revoke') }}</button>
                </form>
            </div>
        @endforeach
    </div>
</section>
