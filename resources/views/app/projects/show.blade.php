<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->name }}
            <span class="ml-2 px-2 py-1 rounded text-xs {{ match($project->status) { 'completed' => 'bg-green-100 text-green-800', 'on_hold' => 'bg-yellow-100 text-yellow-800', default => 'bg-blue-100 text-blue-800' } }}">
                {{ str_replace('_', ' ', ucfirst($project->status)) }}
            </span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <p class="text-sm text-gray-600">{{ $project->description ?? __('No description.') }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-lg mb-4">{{ __('Tasks') }}</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Title') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Assigned To') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Due') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($project->tasks as $task)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $task->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->assignee->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ str_replace('_', ' ', ucfirst($task->status)) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No tasks in this project yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
