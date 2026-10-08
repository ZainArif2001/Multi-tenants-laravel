<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employees') }}
            @can('employees.create')
                <x-button-link href="{{ route('tenant.employees.create') }}" class="ml-4 float-right">
                    {{ __('Add Employee') }}
                </x-button-link>
            @endcan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Email') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Department') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Position') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $employee->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->email ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->department ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->position ?? '—' }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @can('employees.edit')
                                            <a href="{{ route('tenant.employees.edit', $employee) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">{{ __('Edit') }}</a>
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
                                <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No employees yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
