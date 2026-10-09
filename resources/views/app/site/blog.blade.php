<x-tenant-site-layout>
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Blog') }}</h1>

            <div class="grid md:grid-cols-3 gap-6">
                @forelse ($posts as $post)
                    <a href="{{ route('tenant.blog.show', $post) }}" class="block bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                        <h3 class="font-semibold text-lg text-gray-900">{{ $post->title }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ Str::limit($post->body, 150) }}</p>
                        <p class="mt-3 text-xs text-gray-400">{{ $post->created_at->format('d M Y') }} · {{ $post->user->name }}</p>
                    </a>
                @empty
                    <p class="text-gray-500 col-span-3">{{ __('No posts published yet.') }}</p>
                @endforelse
            </div>
        </div>
    </section>
</x-tenant-site-layout>
