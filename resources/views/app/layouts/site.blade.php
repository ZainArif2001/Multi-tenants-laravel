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

        <footer class="border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ tenant('name') }}. {{ __('All rights reserved.') }}
            </div>
        </footer>

        @if (tenant()->hasModule('chat'))
            @include('app.partials.chat-widget')
        @endif
    </body>
</html>
