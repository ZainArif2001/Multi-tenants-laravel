<x-tenant-app-layout>
    <x-slot name="header">{{ __('Posts') }}</x-slot>

    @can('posts.create')
        <x-slot name="actions">
            <x-button-link href="{{ route('tenant.posts.create') }}">
                {{ __('+ New Post') }}
            </x-button-link>
        </x-slot>
    @endcan

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Post') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Category') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Author') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Views') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($posts as $post)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($post->featured_image)
                                                <img src="{{ $post->image_url }}" class="w-10 h-10 rounded object-cover" alt="">
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $post->title }}
                                                    @if ($post->featured)
                                                        <span title="Featured" class="text-amber-500">★</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 font-mono">/{{ $post->slug }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $post->category->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $post->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $post->views }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 rounded text-xs {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm space-x-3">
                                        @if ($post->status === 'published')
                                            <a href="{{ route('tenant.blog.show', $post) }}" target="_blank" class="text-gray-500 hover:text-gray-700">{{ __('View') }}</a>
                                        @endif
                                        @can('posts.edit')
                                            @if ($post->user_id === auth()->id() || auth()->user()->can('posts.publish'))
                                                <a href="{{ route('tenant.posts.edit', $post) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                            @endif
                                        @endcan
                                        @can('posts.delete')
                                            @if ($post->user_id === auth()->id() || auth()->user()->can('posts.publish'))
                                                <form method="POST" action="{{ route('tenant.posts.destroy', $post) }}" class="inline"
                                                    onsubmit="return confirm('{{ __('Delete this post?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No posts yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
