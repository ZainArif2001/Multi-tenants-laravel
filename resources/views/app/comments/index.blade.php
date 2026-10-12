<x-tenant-app-layout>
    <x-slot name="header">{{ __('Comments') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Comment') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Post') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('By') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($comments as $comment)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs">{{ Str::limit($comment->body, 80) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $comment->post->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $comment->name }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs {{ $comment->approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $comment->approved ? 'Approved' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm space-x-3">
                                    @if (! $comment->approved)
                                        <form method="POST" action="{{ route('tenant.comments.approve', $comment) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-green-600 hover:text-green-900">{{ __('Approve') }}</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('tenant.comments.destroy', $comment) }}" class="inline"
                                          onsubmit="return confirm('{{ __('Delete this comment?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No comments yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">{{ $comments->links() }}</div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
