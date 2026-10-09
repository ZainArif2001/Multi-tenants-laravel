<x-tenant-site-layout>
    <!-- Hero -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold text-gray-900">{{ tenant('name') }}</h1>
            <p class="mt-4 text-lg text-gray-600">{{ __('Welcome to our website.') }}</p>
        </div>
    </section>

    <!-- Latest Posts -->
    @if (tenant()->hasModule('posts') && $posts->isNotEmpty())
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Latest Posts') }}</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach ($posts as $post)
                        <a href="{{ route('tenant.blog.show', $post) }}" class="block bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                            <h3 class="font-semibold text-lg text-gray-900">{{ $post->title }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ Str::limit($post->body, 120) }}</p>
                            <p class="mt-3 text-xs text-gray-400">{{ $post->created_at->format('d M Y') }} · {{ $post->user->name }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Team -->
    @if (tenant()->hasModule('employees') && $employees->isNotEmpty())
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ __('Our Team') }}</h2>
                <div class="grid md:grid-cols-4 gap-6">
                    @foreach ($employees as $employee)
                        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
                            <div class="w-14 h-14 mx-auto rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                            <h3 class="mt-3 font-semibold text-gray-900">{{ $employee->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $employee->position ?? $employee->department }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-tenant-site-layout>
