<x-tenant-app-layout>
    <x-slot name="header">{{ __('Employee Profile') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile card -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <span class="w-20 h-20 rounded-full bg-indigo-600 text-white flex items-center justify-center text-3xl font-bold shrink-0">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </span>
                <div class="flex-1 text-center sm:text-left">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $employee->name }}</h2>
                    <p class="text-indigo-600 font-medium">{{ $employee->job_title ?? __('Employee') }}</p>
                    @if ($employee->jobRole)
                        <span class="inline-block mt-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">{{ $employee->jobRole->name }}</span>
                    @endif
                    <div class="mt-3 text-sm text-gray-500 space-y-1">
                        @if ($employee->email)<div>{{ $employee->email }}</div>@endif
                        @if ($employee->phone)<div>{{ $employee->phone }}</div>@endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-center text-sm">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-400 uppercase">{{ __('Department') }}</div>
                        <div class="font-semibold text-gray-800 mt-1">{{ $employee->department->name ?? '—' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-400 uppercase">{{ __('Manager') }}</div>
                        <div class="font-semibold text-gray-800 mt-1">{{ $employee->manager->name ?? '—' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-400 uppercase">{{ __('Salary') }}</div>
                        <div class="font-semibold text-gray-800 mt-1">{{ $employee->salary ? number_format($employee->salary) : '—' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-400 uppercase">{{ __('Hired') }}</div>
                        <div class="font-semibold text-gray-800 mt-1">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <!-- Projects -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">{{ __('Assigned Projects') }}</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Project') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Assigned Hours') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Timeline') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($employee->projects as $project)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $project->name }}</td>
                                <td class="px-6 py-4 text-sm"><span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">{{ $project->status }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $project->pivot->assigned_hours ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $project->start_date?->format('M d, Y') ?? '—' }} → {{ $project->end_date?->format('M d, Y') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No projects assigned.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex gap-3">
                @can('employees.edit')
                    <x-button-link href="{{ route('tenant.employees.edit', $employee) }}">{{ __('Edit') }}</x-button-link>
                @endcan
                <a href="{{ route('tenant.employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">&larr; {{ __('Back to employees') }}</a>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
