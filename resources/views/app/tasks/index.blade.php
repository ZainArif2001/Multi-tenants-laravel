<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tasks') }}
            @can('tasks.create')
                <x-button-link href="{{ route('tenant.tasks.create') }}" class="ml-4 float-right">
                    {{ __('Add Task') }}
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Title') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Project') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Assigned To') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Due') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($tasks as $task)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $task->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->project->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->assignee->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if ($task->assigned_to === auth()->id() && auth()->user()->can('tasks.update_status'))
                                            <form method="POST" action="{{ route('tenant.tasks.status', $task) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()"
                                                    class="text-xs border-gray-300 rounded-md shadow-sm py-1">
                                                    @foreach (['todo' => 'Todo', 'in_progress' => 'In Progress', 'done' => 'Done'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @else
                                            <span class="px-2 py-1 rounded text-xs {{ match($task->status) { 'done' => 'bg-green-100 text-green-800', 'in_progress' => 'bg-yellow-100 text-yellow-800', default => 'bg-gray-100 text-gray-800' } }}">
                                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @can('tasks.edit')
                                            <a href="{{ route('tenant.tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('tasks.delete')
                                            <form method="POST" action="{{ route('tenant.tasks.destroy', $task) }}" class="inline"
                                                onsubmit="return confirm('{{ __('Delete this task?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No tasks yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
