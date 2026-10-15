<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ tenant('name') ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white">
        <header class="border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
                <a href="{{ route('tenant.home') }}" class="font-bold text-lg text-gray-800">
                    {{ tenant('name') }}
                </a>

                <nav class="flex items-center space-x-6 text-sm">
                    <a href="{{ route('tenant.home') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('tenant.home') ? 'font-semibold text-gray-900' : '' }}">
                        {{ __('Home') }}
                    </a>
                    @if (tenant()->hasModule('posts'))
                        <a href="{{ route('tenant.blog') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('tenant.blog*') ? 'font-semibold text-gray-900' : '' }}">
                            {{ __('Blog') }}
                        </a>
                        <a href="{{ route('tenant.categories') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('tenant.categories') || request()->routeIs('tenant.category') ? 'font-semibold text-gray-900' : '' }}">
                            {{ __('Categories') }}
                        </a>
                    @endif
                    @if (tenant()->hasModule('employees'))
                        <a href="{{ route('tenant.team') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('tenant.team') ? 'font-semibold text-gray-900' : '' }}">
                            {{ __('Team') }}
                        </a>
                    @endif
                    @auth
                        <a href="{{ route('tenant.dashboard') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('tenant.login') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        @php($siteSettings = \App\Models\SiteSetting::first())
        <footer class="bg-slate-900 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid md:grid-cols-3 gap-10">
                    <!-- Brand -->
                    <div>
                        <div class="font-bold text-lg text-white">{{ tenant('name') }}</div>
                        <p class="mt-3 text-sm text-slate-400 leading-relaxed">
                            {{ $siteSettings->description ?? $siteSettings->tagline ?? __('Your trusted partner.') }}
                        </p>
                        @if ($siteSettings?->facebook_url || $siteSettings?->twitter_url || $siteSettings?->linkedin_url)
                            <div class="mt-4 flex gap-3">
                                @if ($siteSettings->facebook_url)
                                    <a href="{{ $siteSettings->facebook_url }}" target="_blank" class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>
                                    </a>
                                @endif
                                @if ($siteSettings->twitter_url)
                                    <a href="{{ $siteSettings->twitter_url }}" target="_blank" class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    </a>
                                @endif
                                @if ($siteSettings->linkedin_url)
                                    <a href="{{ $siteSettings->linkedin_url }}" target="_blank" class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 110-4.12 2.06 2.06 0 010 4.12zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Quick links -->
                    <div>
                        <h4 class="text-sm font-semibold text-white uppercase tracking-wider">{{ __('Quick Links') }}</h4>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li><a href="{{ route('tenant.home') }}" class="text-slate-400 hover:text-white transition">{{ __('Home') }}</a></li>
                            @if (tenant()->hasModule('posts'))
                                <li><a href="{{ route('tenant.blog') }}" class="text-slate-400 hover:text-white transition">{{ __('Blog') }}</a></li>
                                <li><a href="{{ route('tenant.categories') }}" class="text-slate-400 hover:text-white transition">{{ __('Categories') }}</a></li>
                            @endif
                            @if (tenant()->hasModule('employees'))
                                <li><a href="{{ route('tenant.team') }}" class="text-slate-400 hover:text-white transition">{{ __('Team') }}</a></li>
                            @endif
                            <li><a href="{{ route('tenant.login') }}" class="text-slate-400 hover:text-white transition">{{ __('Staff Login') }}</a></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="text-sm font-semibold text-white uppercase tracking-wider">{{ __('Contact') }}</h4>
                        <ul class="mt-4 space-y-2 text-sm text-slate-400">
                            @if ($siteSettings?->contact_email)
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <a href="mailto:{{ $siteSettings->contact_email }}" class="hover:text-white transition">{{ $siteSettings->contact_email }}</a>
                                </li>
                            @endif
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                {{ request()->getHost() }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-slate-800 text-center text-xs text-slate-500">
                    &copy; {{ date('Y') }} {{ tenant('name') }}. {{ __('All rights reserved.') }}
                </div>
            </div>
        </footer>

        @if (tenant()->hasModule('chat'))
            @include('app.partials.chat-widget')
        @endif
    </body>
</html>
