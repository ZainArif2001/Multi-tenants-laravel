<x-tenant-app-layout>
    <x-slot name="header">{{ __('Welcome') }}</x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-2xl p-8">
                <div class="text-4xl text-center">👋</div>
                <h2 class="mt-4 text-xl font-bold text-center text-gray-900">{{ __('Welcome to :name', ['name' => tenant('name')]) }}</h2>
                <p class="mt-2 text-sm text-center text-gray-500">{{ __('Quick setup — brand your workspace (takes 10 seconds).') }}</p>

                <form method="POST" action="{{ route('tenant.onboarding.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <x-input-label for="tagline" :value="__('Your tagline')" />
                        <x-text-input id="tagline" name="tagline" class="block mt-1 w-full" type="text"
                                      placeholder="{{ __('We build things people love.') }}" />
                    </div>
                    <div>
                        <x-input-label for="primary_color" :value="__('Brand color')" />
                        <input type="color" id="primary_color" name="primary_color" value="#4f46e5"
                               class="mt-1 w-full h-12 rounded-lg border border-gray-300 cursor-pointer">
                    </div>
                    <button class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition">
                        {{ __('Finish setup →') }}
                    </button>
                    <a href="{{ route('tenant.onboarding') }}?skip=1" onclick="event.preventDefault(); window.location.href='{{ route('tenant.dashboard') }}?onboarded=1'"
                       class="block text-center text-xs text-gray-400 hover:text-gray-600">{{ __('Skip for now') }}</a>
                </form>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
