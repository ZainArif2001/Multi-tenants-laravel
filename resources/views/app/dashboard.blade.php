<x-tenant-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome banner -->
            <div class="bg-gradient-to-r from-slate-900 to-indigo-900 rounded-2xl p-6 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</h3>
                    <p class="text-slate-300 text-sm mt-1">{{ tenant('name') }} · {{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
                </div>
                <a href="{{ route('tenant.home') }}" target="_blank" class="hidden sm:inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 rounded-lg px-4 py-2 text-sm transition">
                    {{ __('View Site') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>

            <!-- Stat cards -->
            @php
                $stats = [
                    ['label' => 'Posts', 'count' => $postsCount, 'can' => 'posts.view', 'module' => 'posts', 'color' => 'indigo', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2'],
                    ['label' => 'Total Views', 'count' => $viewsCount, 'can' => 'posts.view', 'module' => 'posts', 'color' => 'violet', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
                    ['label' => 'Pending Comments', 'count' => $commentsCount, 'can' => 'posts.view', 'module' => 'posts', 'color' => 'amber', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                    ['label' => 'Employees', 'count' => $employeesCount, 'can' => 'employees.view', 'module' => 'employees', 'color' => 'emerald', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4m4 4a4 4 0 00-4 4'],
                    ['label' => 'Projects', 'count' => $projectsCount, 'can' => 'projects.view', 'module' => 'projects', 'color' => 'sky', 'icon' => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z'],
                    ['label' => 'Tasks', 'count' => $tasksCount, 'can' => 'tasks.view', 'module' => 'projects', 'color' => 'rose', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6m-6 4h6m-6 4h6'],
                    ['label' => 'Chat Sessions', 'count' => $chatsCount, 'can' => 'chats.view', 'module' => 'chat', 'color' => 'cyan', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 01-9 9 9.75 9.75 0 01-3.74-.73L3 21l1.23-4.1A9 9 0 1121 12z'],
                    ['label' => 'Users', 'count' => $usersCount, 'can' => 'users.manage', 'module' => null, 'color' => 'slate', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm6-11a4 4 0 11-8 0 4 4 0 018 0z'],
                ];
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($stats as $stat)
                    @if (($stat['module'] === null || (tenant()->hasModule($stat['module']) && auth()->user()->hasAccessTo($stat['module']))) && (auth()->user()->can($stat['can']) || auth()->user()->hasRole('admin')))
                        <div class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md transition">
                            <div class="w-10 h-10 rounded-xl bg-{{ $stat['color'] }}-100 text-{{ $stat['color'] }}-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg>
                            </div>
                            <div class="mt-3 text-2xl font-bold text-gray-900">{{ number_format($stat['count']) }}</div>
                            <div class="text-sm text-gray-500">{{ $stat['label'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Recent activity -->
            <div class="grid lg:grid-cols-2 gap-6">
                @if (tenant()->hasModule('posts') && auth()->user()->hasAccessTo('posts') && $recentPosts->isNotEmpty())
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">{{ __('Recent Posts') }}</h3>
                            <a href="{{ route('tenant.posts.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('View all →') }}</a>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach ($recentPosts as $post)
                                <a href="{{ route('tenant.posts.edit', $post) }}" class="flex items-center gap-3 py-3 group">
                                    @if ($post->image_url)
                                        <img src="{{ $post->image_url }}" class="w-10 h-10 rounded-lg object-cover shrink-0" alt="">
                                    @else
                                        <span class="w-10 h-10 rounded-lg bg-gray-100 shrink-0"></span>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-700 truncate">{{ $post->title }}</p>
                                        <p class="text-xs text-gray-400">{{ $post->category->name ?? __('Uncategorized') }} · {{ $post->views }} views</p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $post->status }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (tenant()->hasModule('posts') && auth()->user()->hasAccessTo('posts') && $recentComments->isNotEmpty())
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">{{ __('Latest Comments') }}</h3>
                            <a href="{{ route('tenant.comments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Moderate →') }}</a>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach ($recentComments as $comment)
                                <div class="py-3">
                                    <p class="text-sm text-gray-700 line-clamp-1">{{ $comment->body }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $comment->name }} {{ __('on') }} {{ $comment->post->title }} ·
                                        @if ($comment->approved)
                                            <span class="text-green-600">{{ __('approved') }}</span>
                                        @else
                                            <span class="text-amber-600">{{ __('pending') }}</span>
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick actions -->
            <div class="flex flex-wrap gap-3">
                @if (tenant()->hasModule('posts') && auth()->user()->hasAccessTo('posts') && auth()->user()->can('posts.create'))
                    <a href="{{ route('tenant.posts.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm hover:bg-indigo-700">
                        + {{ __('New Post') }}
                    </a>
                @endif
                @if (tenant()->hasModule('employees') && auth()->user()->hasAccessTo('employees') && auth()->user()->can('employees.create'))
                    <a href="{{ route('tenant.employees.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        + {{ __('New Employee') }}
                    </a>
                @endif
                @if (tenant()->hasModule('projects') && auth()->user()->hasAccessTo('projects') && auth()->user()->can('projects.manage'))
                    <a href="{{ route('tenant.projects.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        + {{ __('New Project') }}
                    </a>
                @endif
            </div>

            {{-- Activity chart — posts & messages per day (last 14) --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6" x-data="dashboardChart({{ Js::from($chart) }})" x-init="render()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">{{ __('Last 14 days') }}</h3>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-indigo-500 rounded-sm"></span> Posts</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-purple-500 rounded-sm"></span> Messages</span>
                    </div>
                </div>
                <canvas x-ref="canvas" height="80"></canvas>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
            <script>
                function dashboardChart(data) {
                    return {
                        render() {
                            new Chart(this.$refs.canvas, {
                                type: 'line',
                                data: {
                                    labels: data.labels,
                                    datasets: [
                                        { label: 'Posts', data: data.posts, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,.1)', fill: true, tension: .35, pointRadius: 2 },
                                        { label: 'Messages', data: data.messages, borderColor: '#a855f7', backgroundColor: 'rgba(168,85,247,.08)', fill: true, tension: .35, pointRadius: 2 },
                                    ],
                                },
                                options: {
                                    plugins: { legend: { display: false } },
                                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                                },
                            });
                        },
                    };
                }
            </script>
        </div>
    </div>
</x-tenant-app-layout>
