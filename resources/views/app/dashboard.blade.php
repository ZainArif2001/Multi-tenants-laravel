<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in as") }}
                    <span class="font-semibold">{{ auth()->user()->name }}</span>
                    <span class="text-sm text-gray-500">({{ auth()->user()->roles->pluck('name')->join(', ') }})</span>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @php
                    $stats = [
                        ['label' => 'Users', 'count' => $usersCount, 'can' => 'users.manage', 'module' => null],
                        ['label' => 'Posts', 'count' => $postsCount, 'can' => 'posts.view', 'module' => 'posts'],
                        ['label' => 'Employees', 'count' => $employeesCount, 'can' => 'employees.view', 'module' => 'employees'],
                        ['label' => 'Projects', 'count' => $projectsCount, 'can' => 'projects.view', 'module' => 'projects'],
                        ['label' => 'Tasks', 'count' => $tasksCount, 'can' => 'tasks.view', 'module' => 'projects'],
                    ];
                @endphp

                @foreach ($stats as $stat)
                    @if (($stat['module'] === null || (tenant()->hasModule($stat['module']) && auth()->user()->hasAccessTo($stat['module']))) && (auth()->user()->can($stat['can']) || auth()->user()->hasRole('admin')))
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                            <div class="text-3xl font-bold text-gray-800">{{ $stat['count'] }}</div>
                            <div class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</x-tenant-app-layout>
