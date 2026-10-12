<x-tenant-site-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('All Categories') }}</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse ($categories as $category)
                <a href="{{ route('tenant.category', $category) }}" class="bg-white border border-gray-200 rounded-2xl p-6 text-center hover:border-indigo-300 hover:shadow-lg transition">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-bold">
                        {{ strtoupper(substr($category->name, 0, 1)) }}
                    </div>
                    <div class="mt-3 font-semibold text-gray-900">{{ $category->name }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $category->posts_count }} {{ __('posts') }}</div>
                    @if ($category->description)
                        <p class="mt-2 text-xs text-gray-500 line-clamp-2">{{ $category->description }}</p>
                    @endif
                </a>
            @empty
                <p class="text-gray-500 col-span-4">{{ __('No categories yet.') }}</p>
            @endforelse
        </div>
    </div>
</x-tenant-site-layout>
