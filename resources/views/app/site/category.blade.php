<x-tenant-site-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <span class="text-sm text-indigo-600 font-medium">{{ __('Category') }}</span>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-gray-500 mb-8">{{ $category->description }}</p>
        @endif

        <div class="grid md:grid-cols-3 gap-6 mt-8">
            @forelse ($posts as $post)
                <a href="{{ route('tenant.blog.show', $post) }}" class="group bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg transition">
                    @if ($post->featured_image)
                        <img src="{{ $post->image_url }}" class="w-full h-44 object-cover" alt="">
                    @endif
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 group-hover:text-indigo-700 line-clamp-2">{{ $post->title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $post->excerpt }}</p>
                        <p class="mt-3 text-xs text-gray-400">{{ $post->user->name }} · {{ $post->created_at->format('M d, Y') }}</p>
                    </div>
                </a>
            @empty
                <p class="text-gray-500 col-span-3">{{ __('No posts in this category yet.') }}</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $posts->links() }}</div>
    </div>
</x-tenant-site-layout>
