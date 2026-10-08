<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Projects') }}
            @can('projects.manage')
                <x-button-link href="{{ route('tenant.projects.create') }}" class="ml-4 float-right">
                    {{ __('Add Project') }}
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Tasks') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($projects as $project)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <a href="{{ route('tenant.projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $project->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 rounded text-xs {{ match($project->status) { 'completed' => 'bg-green-100 text-green-800', 'on_hold' => 'bg-yellow-100 text-yellow-800', default => 'bg-blue-100 text-blue-800' } }}">
                                            {{ str_replace('_', ' ', ucfirst($project->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $project->tasks_count }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @can('projects.manage')
                                            <a href="{{ route('tenant.projects.edit', $project) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('tenant.projects.destroy', $project) }}" class="inline"
                                                onsubmit="return confirm('{{ __('Delete this project and all its tasks?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No projects yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
