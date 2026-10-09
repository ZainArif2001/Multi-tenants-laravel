<x-tenant-site-layout>
    <section class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('tenant.blog') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; {{ __('Back to blog') }}</a>

            <h1 class="mt-4 text-3xl font-bold text-gray-900">{{ $post->title }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ $post->created_at->format('d M Y') }} · {{ $post->user->name }}</p>

            <div class="mt-8 prose max-w-none text-gray-700 whitespace-pre-line">
                {{ $post->body }}
            </div>
        </div>
    </section>
</x-tenant-site-layout>
