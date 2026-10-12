<x-tenant-site-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        @if ($featured)
            <!-- Hero: featured post -->
            <div class="grid lg:grid-cols-3 gap-8 mb-14">
                <a href="{{ route('tenant.blog.show', $featured) }}" class="lg:col-span-2 group block">
                    <div class="rounded-2xl overflow-hidden mb-4">
                        @if ($featured->featured_image)
                            <img src="{{ $featured->image_url }}" class="w-full h-80 object-cover group-hover:scale-105 transition duration-300" alt="">
                        @else
                            <div class="w-full h-80 bg-gradient-to-br from-indigo-500 to-violet-600"></div>
                        @endif
                    </div>
                    @if ($featured->category)
                        <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $featured->category->name }}</span>
                    @endif
                    <h1 class="mt-2 text-3xl font-bold text-gray-900 group-hover:text-indigo-700 transition">{{ $featured->title }}</h1>
                    <p class="mt-2 text-gray-600">{{ $featured->excerpt }}</p>
                    <p class="mt-3 text-sm text-gray-400">{{ $featured->user->name }} · {{ $featured->created_at->format('M d, Y') }} · {{ $featured->views }} views</p>
                </a>

                <!-- Popular posts -->
                @if ($popular->isNotEmpty())
                    <aside class="bg-white border border-gray-200 rounded-2xl p-6 h-fit">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Popular Posts') }}</h3>
                        <div class="space-y-4">
                            @foreach ($popular as $p)
                                <a href="{{ route('tenant.blog.show', $p) }}" class="flex gap-3 group">
                                    @if ($p->featured_image)
                                        <img src="{{ $p->image_url }}" class="w-16 h-16 rounded-lg object-cover shrink-0" alt="">
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-700 line-clamp-2">{{ $p->title }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $p->views }} {{ __('views') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </aside>
                @endif
            </div>
        @endif

        <!-- Latest articles -->
        @if ($latest->isNotEmpty())
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('Latest Articles') }}</h2>
                <a href="{{ route('tenant.blog') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('View all →') }}</a>
            </div>
            <div class="grid md:grid-cols-3 gap-6 mb-14">
                @foreach ($latest as $post)
                    <a href="{{ route('tenant.blog.show', $post) }}" class="group bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg transition">
                        @if ($post->featured_image)
                            <img src="{{ $post->image_url }}" class="w-full h-44 object-cover" alt="">
                        @endif
                        <div class="p-5">
                            @if ($post->category)
                                <span class="text-xs font-semibold text-indigo-600">{{ $post->category->name }}</span>
                            @endif
                            <h3 class="mt-1 font-semibold text-gray-900 group-hover:text-indigo-700 line-clamp-2">{{ $post->title }}</h3>
                            <p class="mt-2 text-xs text-gray-400">{{ $post->created_at->format('M d, Y') }} · {{ $post->views }} views</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Categories -->
        @if ($categories->isNotEmpty())
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('Categories') }}</h2>
                <a href="{{ route('tenant.categories') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('View all →') }}</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-14">
                @foreach ($categories as $category)
                    <a href="{{ route('tenant.category', $category) }}" class="bg-white border border-gray-200 rounded-xl p-5 text-center hover:border-indigo-300 hover:shadow transition">
                        <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $category->posts_count }} {{ __('posts') }}</div>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Team -->
        @if ($employees->isNotEmpty())
            <section class="bg-gray-50 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 rounded-2xl">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">{{ __('Our Team') }}</h2>
                <div class="grid md:grid-cols-4 gap-6">
                    @foreach ($employees as $employee)
                        <div class="group bg-white border border-gray-200 rounded-2xl p-6 text-center hover:shadow-xl hover:-translate-y-1 transition duration-200">
                            <div class="w-14 h-14 mx-auto rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-xl font-bold shadow-lg shadow-indigo-200">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                            <h3 class="mt-3 font-bold text-gray-900">{{ $employee->name }}</h3>
                            <p class="text-sm text-indigo-600 font-medium">{{ $employee->job_title ?? $employee->department?->name }}</p>
                            @if ($employee->jobRole)
                                <span class="inline-block mt-2 text-xs bg-indigo-50 text-indigo-700 rounded-full px-3 py-1">{{ $employee->jobRole->name }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-8">
                    <a href="{{ route('tenant.team') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('Meet the full team →') }}</a>
                </div>
            </section>
        @endif
    </div>
</x-tenant-site-layout>
