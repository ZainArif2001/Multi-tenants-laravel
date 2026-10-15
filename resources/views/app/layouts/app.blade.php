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
    <body class="font-sans antialiased bg-gray-100">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">

            <!-- Sidebar -->
            <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 transform transition-transform duration-200 lg:translate-x-0 lg:static"
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                <div class="h-16 flex items-center px-6 border-b border-slate-800">
                    <a href="{{ route('tenant.dashboard') }}" class="font-bold text-lg text-white truncate">
                        {{ tenant('name') ?? config('app.name') }}
                    </a>
                </div>

                <nav class="p-4 space-y-1 text-sm">
                    @php
                        $navItem = 'flex items-center gap-3 px-3 py-2 rounded-lg transition';
                        $navActive = 'bg-slate-800 text-white';
                        $navIdle = 'text-slate-400 hover:text-white hover:bg-slate-800';
                    @endphp

                    <a href="{{ route('tenant.dashboard') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.dashboard') ? $navActive : $navIdle }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10"/></svg>
                        Dashboard
                    </a>

                    @if (tenant()->hasModule('posts') && auth()->user()->hasAccessTo('posts'))
                        <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Blog</p>
                        @can('posts.view')
                            <a href="{{ route('tenant.posts.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.posts.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg>
                                Posts
                            </a>
                            <a href="{{ route('tenant.categories.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.categories.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                Categories
                            </a>
                            <a href="{{ route('tenant.comments.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.comments.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Comments
                            </a>
                        @endcan
                    @endif

                    @if (tenant()->hasModule('employees') && auth()->user()->hasAccessTo('employees'))
                        <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">HR</p>
                        @can('employees.view')
                            <a href="{{ route('tenant.employees.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.employees.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4m4 4a4 4 0 00-4 4"/></svg>
                                Employees
                            </a>
                            <a href="{{ route('tenant.departments.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.departments.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Departments
                            </a>
                            <a href="{{ route('tenant.job-roles.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.job-roles.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Job Roles
                            </a>
                            <a href="{{ route('tenant.managers.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.managers.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Managers
                            </a>
                        @endcan
                    @endif

                    @if (tenant()->hasModule('projects') && auth()->user()->hasAccessTo('projects'))
                        @can('projects.view')
                            <a href="{{ route('tenant.projects.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.projects.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                                Projects
                            </a>
                        @endcan
                        @can('tasks.view')
                            <a href="{{ route('tenant.tasks.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.tasks.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6m-6 4h6m-6 4h6m-3 4l2 2 4-4"/></svg>
                                Tasks
                            </a>
                        @endcan
                    @endif

                    @if (tenant()->hasModule('chat') && auth()->user()->hasAccessTo('chat'))
                        @can('chats.view')
                            <a href="{{ route('tenant.chats.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.chats.*') ? $navActive : $navIdle }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Chats
                            </a>
                        @endcan
                    @endif

                    @role('admin')
                        <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Admin</p>
                        <a href="{{ route('tenant.users.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.users.*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm6-11a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Users
                        </a>
                        <a href="{{ route('tenant.settings.edit') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.settings.*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </a>
                    @endrole

                    <p class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Site</p>
                    <a href="{{ route('tenant.home') }}" target="_blank" class="{{ $navItem }} {{ $navIdle }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        View Website
                    </a>
                </nav>
            </aside>

            <!-- Mobile overlay -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
                 class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

            <!-- Main column -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Topbar -->
                <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = ! sidebarOpen" class="lg:hidden text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        @isset($header)
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $header }}</h2>
                        @endisset
                    </div>

                    <div class="flex items-center gap-3">
                        @isset($actions)
                            {{ $actions }}
                        @endisset

                        <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold mr-2">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="hidden sm:block">{{ Auth::user()->name }}</div>
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('tenant.profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('tenant.logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('tenant.logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
