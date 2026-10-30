<x-tenant-app-layout>
    <x-slot name="header">{{ __('Employees') }}</x-slot>

    @can('employees.create')
        <x-slot name="actions">
            <a href="{{ route('tenant.employees.import.form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                {{ __('Import') }}
            </a>
            <x-button-link href="{{ route('tenant.employees.create') }}">
                {{ __('+ New Employee') }}
            </x-button-link>
        </x-slot>
    @endcan

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            @if (session('import_errors'))
                <div class="mb-4 p-4 bg-amber-50 border border-amber-300 text-amber-800 rounded-lg text-sm">
                    <div class="font-semibold mb-1">{{ __('Import warnings:') }}</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Employee') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Department') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Role') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Manager') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Projects') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Hired') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                                            </span>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $employee->job_title ?? $employee->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->department->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->jobRole->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->manager->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->projects_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-6 py-4 text-right text-sm space-x-3">
                                        <a href="{{ route('tenant.employees.show', $employee) }}" class="text-gray-500 hover:text-gray-700">{{ __('View') }}</a>
                                        @can('employees.edit')
                                            <a href="{{ route('tenant.employees.edit', $employee) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('employees.delete')
                                            <form method="POST" action="{{ route('tenant.employees.destroy', $employee) }}" class="inline"
                                                onsubmit="return confirm('{{ __('Delete this employee?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No employees yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $employees->links() }}
        </div>
    </div>
</x-tenant-app-layout>
