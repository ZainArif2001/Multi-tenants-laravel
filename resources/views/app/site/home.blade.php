<x-tenant-site-layout>
    @if ($settings->show_hero)
        <!-- Hero band -->
        <section class="bg-slate-900 py-16 mb-2">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold text-white">{{ $settings->hero_title ?: $settings->tagline ?: tenant('name') }}</h1>
                @if ($settings->hero_subtitle ?: $settings->description)
                    <p class="mt-4 text-slate-300 max-w-2xl mx-auto">{{ $settings->hero_subtitle ?: $settings->description }}</p>
                @endif
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    @if (tenant()->hasModule('posts'))
                        <a href="{{ route('tenant.blog') }}" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 text-sm font-medium">{{ __('Read our blog') }}</a>
                    @endif
                    @if ($settings->contact_email)
                        <a href="mailto:{{ $settings->contact_email }}" class="px-5 py-2.5 bg-white/10 text-white rounded-lg hover:bg-white/20 text-sm font-medium">{{ __('Contact us') }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        @if ($settings->show_posts)
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

        {{-- Contact form → lands in the staff inbox --}}
        @if ($settings->show_contact)
        <section class="border-t border-gray-100 pt-14">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('Get in touch') }}</h2>
                <p class="text-gray-500 text-sm mt-2">{{ __('Have a question? Send us a message and our team will get back to you.') }}</p>

                @if (session('contact_sent'))
                    <div class="mt-6 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-4">
                        {{ __('✓ Message sent! We will get back to you soon.') }}
                    </div>
                @else
                    <form method="POST" action="{{ route('tenant.contact.send') }}" class="mt-8 grid gap-4 text-left">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-4">
                            <input name="name" required placeholder="{{ __('Your name') }}"
                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm px-4 py-3">
                            <input name="email" type="email" required placeholder="{{ __('Email address') }}"
                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm px-4 py-3">
                        </div>
                        <input name="subject" placeholder="{{ __('Subject (optional)') }}"
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm px-4 py-3">
                        <textarea name="message" required rows="4" placeholder="{{ __('How can we help?') }}"
                                  class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm px-4 py-3"></textarea>
                        <button class="justify-self-center px-8 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition">
                            {{ __('Send message') }}
                        </button>
                    </form>
                @endif
            </div>
        </section>
        @endif
    </div>
</x-tenant-site-layout>
