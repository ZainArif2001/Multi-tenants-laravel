<div class="grid lg:grid-cols-3 gap-6">
    <!-- Main column -->
    <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
        <div>
            <x-input-label for="title" :value="__('Title')" />
            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                :value="old('title', $post?->title)" required autofocus />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="slug" :value="__('Slug (leave blank to auto-generate)')" />
            <x-text-input id="slug" class="block mt-1 w-full font-mono text-sm" type="text" name="slug"
                :value="old('slug', $post?->slug)" />
            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="excerpt" :value="__('Excerpt')" />
            <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('excerpt', $post?->excerpt) }}</textarea>
            <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="body" :value="__('Content')" />
            <textarea id="body" name="body" rows="14"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm">{{ old('body', $post?->body) }}</textarea>
            <x-input-error :messages="$errors->get('body')" class="mt-2" />
        </div>
    </div>

    <!-- Sidebar column -->
    <div class="space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800 text-sm">{{ __('Post Settings') }}</h3>

            <div>
                <x-input-label for="category_id" :value="__('Category')" />
                <select id="category_id" name="category_id"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}" @selected(old('category_id', $post?->category_id) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tags" :value="__('Tags (comma separated)')" />
                <x-text-input id="tags" class="block mt-1 w-full" type="text" name="tags"
                    :value="old('tags', $post?->tags->pluck('name')->join(', '))" placeholder="laravel, php, tips" />
            </div>

            @can('posts.publish')
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="publish" value="1"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        @checked(old('publish', $post?->status === 'published'))>
                    {{ __('Published') }}
                </label>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="featured" value="1"
                        class="rounded border-gray-300 text-amber-500 shadow-sm focus:ring-amber-500"
                        @checked(old('featured', $post?->featured))>
                    {{ __('Featured (hero on homepage)') }}
                </label>
            @else
                <p class="text-xs text-gray-500">{{ __('Post will be saved as draft — an admin can publish it.') }}</p>
            @endcan
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2"
             x-data="{ img: '{{ $post?->image_url }}' }">
            <h3 class="font-semibold text-gray-800 text-sm">{{ __('Featured Image') }}</h3>
            <label for="featured_image" class="block border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-indigo-400 transition">
                <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="mt-1 block text-sm text-gray-500">{{ __('Click to upload image') }}</span>
                <span class="block text-xs text-gray-400">{{ __('JPG, PNG, WEBP — max 2MB') }}</span>
                <input id="featured_image" type="file" name="featured_image" accept="image/*" class="hidden"
                    @change="img = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : img" />
            </label>
            <x-input-error :messages="$errors->get('featured_image')" class="mt-2" />
            <template x-if="img">
                <img :src="img" class="mt-2 w-full h-32 object-cover rounded-lg" alt="">
            </template>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <x-primary-button class="w-full justify-center">
                {{ $post ? __('Update Post') : __('Save Post') }}
            </x-primary-button>
        </div>
    </div>
</div>
