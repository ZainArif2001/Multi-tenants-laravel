<x-tenant-site-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Author card -->
        <div class="bg-white border border-gray-200 rounded-2xl p-8 mb-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <span class="w-20 h-20 rounded-full bg-indigo-600 text-white flex items-center justify-center text-3xl font-bold shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="mt-1 text-gray-600">{{ $user->bio ?? __('Author at :name', ['name' => tenant('name')]) }}</p>
            </div>
            <div class="flex gap-6 text-center">
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['posts'] }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">{{ __('Articles') }}</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['views']) }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">{{ __('Views') }}</div>
                </div>
            </div>
        </div>

        <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('Articles by :name', ['name' => $user->name]) }}</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @forelse ($posts as $post)
                <a href="{{ route('tenant.blog.show', $post) }}" class="group bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg transition">
                    @if ($post->featured_image)
                        <img src="{{ $post->image_url }}" class="w-full h-44 object-cover" alt="">
                    @endif
                    <div class="p-5">
                        @if ($post->category)
                            <span class="text-xs font-semibold text-indigo-600">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="mt-1 font-semibold text-gray-900 group-hover:text-indigo-700 line-clamp-2">{{ $post->title }}</h3>
                        <p class="mt-3 text-xs text-gray-400">{{ $post->created_at->format('M d, Y') }}</p>
                    </div>
                </a>
            @empty
                <p class="text-gray-500 col-span-3">{{ __('No published posts yet.') }}</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $posts->links() }}</div>
    </div>
</x-tenant-site-layout>
