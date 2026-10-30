<x-tenant-app-layout>
    <x-slot name="header">{{ __('Site Settings') }}</x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('tenant.settings.update') }}" enctype="multipart/form-data" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
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

                <div class="border-t pt-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Site Builder') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="logo" :value="__('Site logo')" />
                            @if ($settings->logo_url)
                                <div class="mt-1 mb-2 flex items-center gap-3">
                                    <img src="{{ $settings->logo_url }}" class="h-10 rounded" alt="Logo">
                                    <span class="text-xs text-gray-400">{{ __('Current logo — upload to replace') }}</span>
                                </div>
                            @endif
                            <input id="logo" name="logo" type="file" accept="image/*"
                                   class="block mt-1 w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="hero_title" :value="__('Hero title')" />
                                <x-text-input id="hero_title" name="hero_title" class="block mt-1 w-full" type="text"
                                    :value="old('hero_title', $settings->hero_title)" :placeholder="tenant('name')" />
                            </div>
                            <div>
                                <x-input-label for="hero_subtitle" :value="__('Hero subtitle')" />
                                <x-text-input id="hero_subtitle" name="hero_subtitle" class="block mt-1 w-full" type="text"
                                    :value="old('hero_subtitle', $settings->hero_subtitle)" placeholder="A short welcome message" />
                            </div>
                        </div>

                        <div>
                            <x-input-label :value="__('Homepage sections')" />
                            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="show_hero" value="1" @checked(old('show_hero', $settings->show_hero))
                                           class="rounded border-gray-300 text-indigo-600">
                                    {{ __('Hero banner') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="show_posts" value="1" @checked(old('show_posts', $settings->show_posts))
                                           class="rounded border-gray-300 text-indigo-600">
                                    {{ __('Blog posts') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="show_contact" value="1" @checked(old('show_contact', $settings->show_contact))
                                           class="rounded border-gray-300 text-indigo-600">
                                    {{ __('Contact form') }}
                                </label>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="footer_text" :value="__('Footer text')" />
                            <x-text-input id="footer_text" name="footer_text" class="block mt-1 w-full" type="text"
                                :value="old('footer_text', $settings->footer_text)" placeholder="e.g. Built with ❤ in Pakistan" />
                        </div>
                    </div>
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

            {{-- Chatbot embed — external websites load this tenant's AI assistant --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ __('🤖 Embed chatbot on any website') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Paste this snippet before the closing') }} <code class="text-xs bg-gray-100 px-1 rounded">&lt;/body&gt;</code>
                        {{ __('tag of any website — your AI assistant will appear as a floating chat bubble.') }}
                    </p>
                </div>

                <div class="relative">
                    <pre id="embedSnippet" class="bg-slate-900 text-emerald-300 text-xs rounded-xl p-4 overflow-x-auto select-all">&lt;script src="{{ url('/embed/'.$embedKey.'/chat.js') }}"&gt;&lt;/script&gt;</pre>
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('embedSnippet').textContent).then(()=>{this.textContent='{{ __('Copied!') }}';setTimeout(()=>this.textContent='{{ __('Copy') }}',1500)})"
                            class="absolute top-2 right-2 text-xs bg-white/10 hover:bg-white/20 text-white rounded-lg px-3 py-1.5 transition">
                        {{ __('Copy') }}
                    </button>
                </div>

                <form method="POST" action="{{ route('tenant.settings.embed-key') }}" class="flex items-center gap-3"
                      onsubmit="return confirm('{{ __('Regenerate? External sites using the old snippet will stop working.') }}')">
                    @csrf
                    <button class="text-xs font-medium text-red-600 hover:text-red-800">{{ __('Regenerate key') }}</button>
                    <span class="text-xs text-gray-400">{{ __('Key') }}: <code class="bg-gray-100 px-1 rounded">{{ $embedKey }}</code></span>
                </form>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
