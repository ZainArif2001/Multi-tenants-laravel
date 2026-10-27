<x-tenant-app-layout>
    <x-slot name="header">{{ __('Plan & Usage') }}</x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-wide text-indigo-200">{{ __('Current plan') }}</div>
                    <div class="text-2xl font-bold mt-1">{{ $planLabel }}</div>
                </div>
                <span class="text-xs bg-white/20 rounded-full px-3 py-1.5">{{ __('Contact support to change plans') }}</span>
            </div>

            {{-- Stripe upgrade buttons — shown when STRIPE_SECRET is configured --}}
            @if (config('services.stripe.secret'))
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Upgrade plan') }}</h3>
                    <div class="flex gap-3">
                        @foreach (['business', 'agency'] as $planName)
                            @if (($plan ?? '') !== $planName)
                                <form method="POST" action="{{ route('tenant.billing.checkout', $planName) }}">
                                    @csrf
                                    <button class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700">
                                        {{ __('Upgrade to :plan', ['plan' => ucfirst($planName)]) }} →
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Enabled modules') }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Tenant::MODULES as $key => $label)
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ in_array($key, $modules) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400 line-through' }}">
                            {{ $label }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Custom domain --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900">{{ __('Custom domain') }}</h3>
                @php($t = tenant())
                <p class="text-xs text-gray-400 mt-1">{{ __('Current') }}: {{ request()->getHost() }}</p>
                @if ($t->pending_domain)
                    <div class="mt-3 text-sm bg-amber-50 border border-amber-200 text-amber-700 rounded-lg px-4 py-2">
                        ⏳ <strong>{{ $t->pending_domain }}</strong> — {{ __('pending approval. Point it to our servers via CNAME.') }}
                    </div>
                @else
                    <form method="POST" action="{{ route('tenant.billing.domain') }}" class="mt-3 flex gap-2">
                        @csrf
                        <input name="domain" placeholder="app.yourbusiness.com" required
                               class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm font-mono">
                        <button class="px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700">{{ __('Connect') }}</button>
                    </form>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @foreach ($stats as $label => $value)
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $value }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-tenant-app-layout>
