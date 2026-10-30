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

        <!-- Reverb websocket config — read before app.js boots Echo -->
        <script>
            window.__reverb = {
                key: @json(config('broadcasting.connections.reverb.key')),
                port: {{ config('broadcasting.connections.reverb.options.port') ?? 6001 }},
                scheme: @json(config('broadcasting.connections.reverb.options.scheme') ?? 'http'),
            };
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            /* Pragmatic dark mode — blanket overrides on common surfaces */
            html.dark body { background: #0f172a !important; }
            html.dark .bg-white { background-color: #1e293b !important; }
            html.dark .bg-gray-100, html.dark .bg-gray-50, html.dark .bg-\[\#efeae2\], html.dark main { background-color: #0f172a !important; }
            html.dark .text-gray-900, html.dark .text-gray-800, html.dark .text-gray-700 { color: #e2e8f0 !important; }
            html.dark .text-gray-600, html.dark .text-gray-500 { color: #94a3b8 !important; }
            html.dark .text-gray-400 { color: #64748b !important; }
            html.dark .border-gray-200, html.dark .border-gray-100, html.dark .border-gray-300, html.dark .divide-gray-100 > *, html.dark .divide-gray-200 > * { border-color: #334155 !important; }
            html.dark .hover\:bg-gray-50:hover, html.dark .hover\:bg-gray-100:hover { background-color: #334155 !important; }
            html.dark input, html.dark select, html.dark textarea { background-color: #1e293b; color: #e2e8f0; border-color: #334155; }
            html.dark .shadow { box-shadow: 0 1px 3px rgba(0,0,0,.4) !important; }
        </style>
        @if (tenant('custom_css'))
            <style>{!! tenant('custom_css') !!}</style>
        @endif
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

                    {{-- Leaves — every member submits own requests, admin reviews --}}
                    @if (tenant()->hasModule('employees'))
                        <a href="{{ route('tenant.leaves.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.leaves.*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            My Leaves
                        </a>
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

                    @if (tenant()->hasModule('messaging') && auth()->user()->hasAccessTo('messaging'))
                        @php($unreadMsgs = \App\Models\MessageReceipt::where('user_id', auth()->id())->whereNull('read_at')->count())
                        <a href="{{ route('tenant.messages.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.messages.*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V10a2 2 0 012-2h2m8-4v4l-4-3h8a2 2 0 012 2z"/></svg>
                            Messages
                            @if ($unreadMsgs > 0)
                                <span class="ml-auto bg-indigo-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center">{{ $unreadMsgs }}</span>
                            @endif
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('admin'))
                        <a href="{{ route('tenant.inbox') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.inbox') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Inbox
                        </a>
                        <a href="{{ route('tenant.activity') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.activity') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Activity
                        </a>
                        <a href="{{ route('tenant.billing') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.billing') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Plan & Usage
                        </a>
                        <a href="{{ route('tenant.customizations') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.customizations*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            Customize
                        </a>
                        <a href="{{ route('tenant.roles.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.roles.*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Roles
                        </a>
                        <a href="{{ route('tenant.webhooks.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.webhooks.*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            Webhooks
                        </a>
                    @endif

                    @if (tenant()->hasModule('chat') && auth()->user()->hasAccessTo('chat') && auth()->user()->hasRole('admin'))
                        <a href="{{ route('tenant.knowledge.index') }}" class="{{ $navItem }} {{ request()->routeIs('tenant.knowledge.*') ? $navActive : $navIdle }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            AI Knowledge
                        </a>
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
                        {{-- Global search --}}
                        <form method="GET" action="{{ route('tenant.search') }}" class="hidden md:block">
                            <input name="q" value="{{ request('q') }}" type="search" placeholder="Search..."
                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm w-44 px-3 py-1.5">
                        </form>
                        @isset($actions)
                            {{ $actions }}
                        @endisset

                        {{-- Notification bell — unread message count, polls every 15s --}}
                        {{-- Unified notification bell — all app events, polls every 15s --}}
                        @php($bellUnread = \App\Models\UserNotification::where('user_id', auth()->id())->whereNull('read_at')->count())
                        <div x-data="{
                                open: false,
                                unread: {{ $bellUnread }},
                                items: [],
                                async refreshBell() {
                                    try {
                                        const r = await fetch('{{ route('tenant.notifications') }}');
                                        const d = await r.json();
                                        this.unread = d.unread;
                                        this.items = d.items;
                                    } catch (e) {}
                                },
                                async markAllRead() {
                                    this.unread = 0;
                                    this.items = this.items.map(n => ({ ...n, read_at: new Date().toISOString() }));
                                    await fetch('{{ route('tenant.notifications.read-all') }}', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    });
                                },
                                async dismiss(id) {
                                    this.items = this.items.filter(n => n.id !== id);
                                    await fetch(`{{ url('/notifications') }}/${id}`, {
                                        method: 'DELETE',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    });
                                },
                                async clearAll() {
                                    this.items = [];
                                    this.unread = 0;
                                    await fetch('{{ route('tenant.notifications.clear') }}', {
                                        method: 'DELETE',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    });
                                },
                                icon(t) {
                                    return { message: '💬', contact: '📩', comment: '💭', task: '✅', employee: '👤', system: '🔔' }[t] || '🔔';
                                },
                                rel(ts) {
                                    const m = Math.floor((Date.now() - new Date(ts)) / 60000);
                                    return m < 1 ? 'just now' : m < 60 ? m + 'm ago' : Math.floor(m / 60) + 'h ago';
                                },
                             }"
                             x-init="refreshBell(); setInterval(() => refreshBell(), 15000)" class="relative">
                            <button @click="open = ! open; if (open) markAllRead()" class="relative p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span x-show="unread > 0" x-text="unread" class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full px-1 min-w-[16px] h-4 flex items-center justify-center"></span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">
                                <div class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase border-b flex items-center justify-between">
                                    {{ __('Notifications') }}
                                    <button x-show="items.length" @click="clearAll()" class="text-[10px] font-medium text-red-500 hover:text-red-700 normal-case">{{ __('Clear all') }}</button>
                                </div>
                                <template x-if="! items.length">
                                    <p class="px-4 py-6 text-sm text-gray-400 text-center">{{ __('All caught up 🎉') }}</p>
                                </template>
                                <div class="max-h-80 overflow-y-auto">
                                    <template x-for="n in items" :key="n.id">
                                        <a :href="n.url || '#'" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100"
                                           :class="{ 'bg-indigo-50/50': ! n.read_at }">
                                            <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm shrink-0" x-text="icon(n.type)"></span>
                                            <span class="flex-1 min-w-0">
                                                <span class="block text-sm font-medium text-gray-900" x-text="n.title"></span>
                                                <span class="block text-xs text-gray-500 truncate" x-text="n.body || ''"></span>
                                                <span class="block text-[10px] text-gray-400 mt-0.5" x-text="rel(n.created_at)"></span>
                                            </span>
                                            <button @click.prevent.stop="dismiss(n.id)" class="text-gray-300 hover:text-red-500 text-sm shrink-0 px-1" title="Dismiss">×</button>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Dark mode toggle --}}
                        <button @click="dark = ! dark; localStorage.setItem('dark', dark ? '1' : '0'); document.documentElement.classList.toggle('dark', dark)"
                                x-data="{ dark: localStorage.getItem('dark') === '1' }" x-init="document.documentElement.classList.toggle('dark', dark)"
                                class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition" title="{{ __('Toggle dark mode') }}">
                            <svg x-show="! dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </button>

                        <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                @if (Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" class="w-8 h-8 rounded-full object-cover mr-2" alt="avatar">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold mr-2">
                                        {{ Auth::user()->initial }}
                                    </div>
                                @endif
                                <div class="hidden sm:block">{{ Auth::user()->name }}
                                    @unless (Auth::user()->hasVerifiedEmail())
                                        <a href="{{ route('tenant.verification.notice') }}" class="text-amber-500 text-xs" title="{{ __('Email not verified — click to verify') }}">⚠️</a>
                                    @endunless
                                </div>
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

        {{-- Ctrl+K command palette — searches posts/employees/projects, quick nav --}}
        <div x-data="{
                open: false,
                q: '',
                results: [],
                selected: 0,
                async search() {
                    if (this.q.trim().length < 2) { this.results = []; return; }
                    const r = await fetch(`{{ route('tenant.search') }}?q=${encodeURIComponent(this.q)}`, { headers: { 'Accept': 'application/json' } });
                    const d = await r.json();
                    this.results = d.results || [];
                    this.selected = 0;
                },
                go(url) { window.location.href = url; },
             }"
             x-init="window.addEventListener('keydown', e => {
                 if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); open = ! open; q = ''; results = []; }
                 if (! open) return;
                 if (e.key === 'Escape') open = false;
                 if (e.key === 'ArrowDown') { e.preventDefault(); selected = Math.min(selected + 1, results.length - 1); }
                 if (e.key === 'ArrowUp') { e.preventDefault(); selected = Math.max(selected - 1, 0); }
                 if (e.key === 'Enter' && results[selected]) go(results[selected].url);
             })"
             @keydown.ctrl.k.window.prevent="open = ! open">
            <div x-show="open" x-cloak class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-start justify-center pt-[15vh]" @click.self="open = false">
                <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden" @keydown.escape.window="open = false">
                    <div class="flex items-center gap-3 px-4 border-b border-gray-200">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input x-model="q" @input.debounce.200ms="search()" x-ref="palette" x-init="$watch('open', v => v && $nextTick(() => $refs.palette.focus()))"
                               placeholder="{{ __('Search posts, employees, projects...') }}"
                               class="w-full py-3.5 text-sm border-0 focus:ring-0 focus:outline-none">
                        <kbd class="text-[10px] text-gray-400 border border-gray-200 rounded px-1.5 py-0.5">ESC</kbd>
                    </div>
                    <div class="max-h-72 overflow-y-auto" x-show="results.length">
                        <template x-for="(r, i) in results" :key="i">
                            <button @click="go(r.url)" :class="selected === i ? 'bg-indigo-50' : ''"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-indigo-50">
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 w-20 text-center shrink-0" x-text="r.type"></span>
                                <span class="text-sm text-gray-800 truncate" x-text="r.title"></span>
                            </button>
                        </template>
                    </div>
                    <div x-show="q.length >= 2 && ! results.length" class="px-4 py-8 text-sm text-gray-400 text-center">{{ __('No results') }}</div>
                    <div class="px-4 py-2 bg-gray-50 border-t text-[10px] text-gray-400 flex gap-4">
                        <span>↑↓ {{ __('navigate') }}</span><span>↵ {{ __('open') }}</span><span>esc {{ __('close') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Global toasts — any component can dispatch a 'toast' event --}}
        <div x-data="{ toasts: [] }"
             @toast.window="toasts.push({ id: Date.now(), text: $event.detail }); setTimeout(() => toasts.shift(), 4000)"
             class="fixed bottom-5 right-5 z-[60] space-y-2 pointer-events-none">
            <template x-for="t in toasts" :key="t.id">
                <div x-transition class="bg-slate-900 text-white text-sm px-4 py-3 rounded-xl shadow-lg max-w-xs pointer-events-auto"
                     x-text="t.text"></div>
            </template>
        </div>
    </body>
</html>
