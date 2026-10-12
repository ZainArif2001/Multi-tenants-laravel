<x-tenant-site-layout>
    <article class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('tenant.blog') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; {{ __('Back to blog') }}</a>

            @if ($post->category)
                <a href="{{ route('tenant.category', $post->category) }}" class="block mt-4 text-xs font-semibold uppercase tracking-wide text-indigo-600">
                    {{ $post->category->name }}
                </a>
            @endif
            <h1 class="mt-2 text-4xl font-bold text-gray-900">{{ $post->title }}</h1>

            <div class="mt-4 flex items-center gap-3 text-sm text-gray-500">
                <a href="{{ route('tenant.author', $post->user) }}" class="flex items-center gap-2 hover:text-gray-900">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </span>
                    {{ $post->user->name }}
                </a>
                <span>·</span>
                <span>{{ $post->created_at->format('M d, Y') }}</span>
                <span>·</span>
                <span>{{ $post->views }} {{ __('views') }}</span>
            </div>

            @if ($post->featured_image)
                <img src="{{ $post->image_url }}" class="mt-8 w-full rounded-2xl object-cover max-h-96" alt="">
            @endif

            <div class="mt-8 text-gray-700 leading-relaxed whitespace-pre-line text-[17px]">
                {{ $post->body }}
            </div>

            @if ($post->tags->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-3 py-1">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif

            <!-- Author card -->
            <a href="{{ route('tenant.author', $post->user) }}" class="mt-10 flex items-center gap-4 bg-gray-50 border border-gray-200 rounded-2xl p-5 hover:shadow transition">
                <span class="w-14 h-14 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xl font-bold shrink-0">
                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                </span>
                <div>
                    <div class="font-semibold text-gray-900">{{ $post->user->name }}</div>
                    <div class="text-sm text-gray-500">{{ $post->user->bio ?: __('Writer') }}</div>
                </div>
            </a>
        </div>

        <!-- Related posts -->
        @if ($related->isNotEmpty())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('Related Posts') }}</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach ($related as $r)
                        <a href="{{ route('tenant.blog.show', $r) }}" class="group bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg transition">
                            @if ($r->featured_image)
                                <img src="{{ $r->image_url }}" class="w-full h-40 object-cover" alt="">
                            @endif
                            <div class="p-5">
                                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-700 line-clamp-2">{{ $r->title }}</h3>
                                <p class="mt-2 text-xs text-gray-400">{{ $r->created_at->format('M d, Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Comments -->
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
            <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('Comments') }} ({{ $comments->count() }})</h2>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="space-y-4 mb-8">
                @forelse ($comments as $comment)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($comment->name, 0, 1)) }}
                            </span>
                            <span class="text-sm font-semibold text-gray-900">{{ $comment->name }}</span>
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-700">{{ $comment->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('Be the first to comment.') }}</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('tenant.blog.comment', $post) }}" class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                @csrf
                <h3 class="font-semibold text-gray-900">{{ __('Leave a comment') }}</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="cname" :value="__('Name')" />
                        <x-text-input id="cname" name="name" class="block mt-1 w-full" required :value="old('name')" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="cemail" :value="__('Email (optional)')" />
                        <x-text-input id="cemail" name="email" type="email" class="block mt-1 w-full" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                </div>
                <div>
                    <x-input-label for="cbody" :value="__('Comment')" />
                    <textarea id="cbody" name="body" rows="3" required
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('body') }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-1" />
                </div>
                <x-primary-button>{{ __('Post Comment') }}</x-primary-button>
            </form>
        </div>
    </article>
</x-tenant-site-layout>
