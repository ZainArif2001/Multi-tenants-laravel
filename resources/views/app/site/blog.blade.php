<x-tenant-site-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Blog') }}</h1>

        <div class="grid lg:grid-cols-3 gap-10">
            <!-- Posts grid -->
            <div class="lg:col-span-2">
                <div class="grid md:grid-cols-2 gap-6">
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
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $post->excerpt }}</p>
                                <p class="mt-3 text-xs text-gray-400">{{ $post->user->name }} · {{ $post->created_at->format('M d, Y') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-500 col-span-2">{{ __('No posts published yet.') }}</p>
                    @endforelse
                </div>

                <div class="mt-8">{{ $posts->links() }}</div>
            </div>

            <!-- Sidebar -->
            <aside class="space-y-6">
                @if ($categories->isNotEmpty())
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Categories') }}</h3>
                        <div class="space-y-2">
                            @foreach ($categories as $category)
                                <a href="{{ route('tenant.category', $category) }}" class="flex justify-between items-center text-sm text-gray-600 hover:text-indigo-700">
                                    <span>{{ $category->name }}</span>
                                    <span class="text-xs bg-gray-100 rounded-full px-2 py-0.5">{{ $category->posts_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($popular->isNotEmpty())
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Popular Posts') }}</h3>
                        <div class="space-y-4">
                            @foreach ($popular as $p)
                                <a href="{{ route('tenant.blog.show', $p) }}" class="flex gap-3 group">
                                    @if ($p->featured_image)
                                        <img src="{{ $p->image_url }}" class="w-14 h-14 rounded-lg object-cover shrink-0" alt="">
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-700 line-clamp-2">{{ $p->title }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $p->views }} {{ __('views') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-tenant-site-layout>
