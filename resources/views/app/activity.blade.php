<x-tenant-app-layout>
    <x-slot name="header">{{ __('Activity Log') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden border border-gray-200 sm:rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('When') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('User') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $log->action === 'created' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $log->action === 'updated' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $log->action === 'deleted' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">{{ __('No activity recorded yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
