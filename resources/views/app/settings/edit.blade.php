<x-tenant-app-layout>
    <x-slot name="header">{{ __('Site Settings') }}</x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('tenant.settings.update') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                @csrf
                @method('PUT')

                <p class="text-sm text-gray-500">{{ __('This branding appears on your public website (hero, footer, social links).') }}</p>

                <div>
                    <x-input-label for="primary_color" :value="__('Brand color')" />
                    <div class="mt-1 flex items-center gap-3">
                        <input type="color" id="primary_color" name="primary_color"
                               value="{{ old('primary_color', tenant('primary_color') ?? '#4f46e5') }}"
                               class="w-12 h-9 rounded border border-gray-300 cursor-pointer">
                        <span class="text-xs text-gray-400">{{ __('Buttons, links and accents on your public site') }}</span>
                    </div>
                    <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tagline" :value="__('Tagline')" />
                    <x-text-input id="tagline" name="tagline" class="block mt-1 w-full" type="text"
                        :value="old('tagline', $settings->tagline)" placeholder="We build things people love." />
                    <x-input-error :messages="$errors->get('tagline')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('About / Description')" />
                    <textarea id="description" name="description" rows="4"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="A short paragraph about your business...">{{ old('description', $settings->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="contact_email" :value="__('Contact Email')" />
                    <x-text-input id="contact_email" name="contact_email" class="block mt-1 w-full" type="email"
                        :value="old('contact_email', $settings->contact_email)" placeholder="hello@yourcompany.com" />
                    <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Social Links') }}</h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="facebook_url" :value="__('Facebook')" />
                            <x-text-input id="facebook_url" name="facebook_url" class="block mt-1 w-full" type="url"
                                :value="old('facebook_url', $settings->facebook_url)" placeholder="https://facebook.com/..." />
                        </div>
                        <div>
                            <x-input-label for="twitter_url" :value="__('Twitter / X')" />
                            <x-text-input id="twitter_url" name="twitter_url" class="block mt-1 w-full" type="url"
                                :value="old('twitter_url', $settings->twitter_url)" placeholder="https://x.com/..." />
                        </div>
                        <div>
                            <x-input-label for="linkedin_url" :value="__('LinkedIn')" />
                            <x-text-input id="linkedin_url" name="linkedin_url" class="block mt-1 w-full" type="url"
                                :value="old('linkedin_url', $settings->linkedin_url)" placeholder="https://linkedin.com/..." />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('facebook_url')" class="mt-2" />
                    <x-input-error :messages="$errors->get('twitter_url')" class="mt-2" />
                    <x-input-error :messages="$errors->get('linkedin_url')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-tenant-app-layout>
