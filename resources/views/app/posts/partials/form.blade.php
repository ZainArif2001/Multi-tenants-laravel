<script src="https://unpkg.com/@tiptap/core@2.11.5/dist/index.umd.min.js"></script>
<script src="https://unpkg.com/@tiptap/starter-kit@2.11.5/dist/index.umd.min.js"></script>
<script src="https://unpkg.com/@tiptap/extension-link@2.11.5/dist/index.umd.min.js"></script>
<script>
    function richEditor(initialHtml) {
        return {
            editor: null,
            html: initialHtml,
            init() {
                this.editor = new window.TiptapCore.Editor({
                    element: this.$refs.editor,
                    extensions: [
                        window.TiptapStarterKit.StarterKit,
                        window.TiptapExtensionLink.Link.configure({ openOnClick: false }),
                    ],
                    content: initialHtml,
                    onUpdate: ({ editor }) => { this.html = editor.getHTML(); },
                });
            },
            chain() { return this.editor.chain().focus(); },
            cmd(name, ...args) { this.editor.chain().focus()[name](...args).run(); },
            isActive(name, attrs) { return this.editor?.isActive(name, attrs); },
            addLink() {
                const url = prompt('{{ __('Link URL') }}', 'https://');
                if (url) this.chain().setLink({ href: url }).run();
            },
        };
    }
</script>

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
            {{-- TipTap rich text editor — saves HTML into the hidden #body field --}}
            <div x-data="richEditor(@js(old('body', $post?->body ?? '')))" class="border border-gray-300 rounded-lg overflow-hidden">
                <div class="flex flex-wrap gap-1 bg-gray-50 border-b border-gray-200 px-2 py-1.5">
                    <button type="button" @click="cmd('toggleBold')" :class="isActive('bold') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500'"
                            class="px-2 py-1 rounded text-xs font-bold hover:bg-gray-200">B</button>
                    <button type="button" @click="cmd('toggleItalic')" :class="isActive('italic') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500'"
                            class="px-2 py-1 rounded text-xs italic hover:bg-gray-200">I</button>
                    <button type="button" @click="chain().toggleHeading({level:2}).run()" :class="isActive('heading', {level:2}) ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500'"
                            class="px-2 py-1 rounded text-xs font-semibold hover:bg-gray-200">H2</button>
                    <button type="button" @click="chain().toggleHeading({level:3}).run()" :class="isActive('heading', {level:3}) ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500'"
                            class="px-2 py-1 rounded text-xs font-semibold hover:bg-gray-200">H3</button>
                    <button type="button" @click="cmd('toggleBulletList')" :class="isActive('bulletList') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500'"
                            class="px-2 py-1 rounded text-xs hover:bg-gray-200">• List</button>
                    <button type="button" @click="cmd('toggleOrderedList')" :class="isActive('orderedList') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500'"
                            class="px-2 py-1 rounded text-xs hover:bg-gray-200">1. List</button>
                    <button type="button" @click="cmd('toggleBlockquote')" :class="isActive('blockquote') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500'"
                            class="px-2 py-1 rounded text-xs hover:bg-gray-200">❝ Quote</button>
                    <button type="button" @click="addLink()" class="px-2 py-1 rounded text-xs text-gray-500 hover:bg-gray-200">🔗 Link</button>
                    <button type="button" @click="cmd('setHorizontalRule')" class="px-2 py-1 rounded text-xs text-gray-500 hover:bg-gray-200">―</button>
                    <button type="button" @click="cmd('undo')" class="px-2 py-1 rounded text-xs text-gray-500 hover:bg-gray-200">↩</button>
                    <button type="button" @click="cmd('redo')" class="px-2 py-1 rounded text-xs text-gray-500 hover:bg-gray-200">↪</button>
                </div>
                <div x-ref="editor" class="prose max-w-none min-h-[300px] px-4 py-3 text-sm focus:outline-none [&_.ProseMirror]:outline-none [&_.ProseMirror]:min-h-[300px]"></div>
            </div>
            <input type="hidden" id="body" name="body" :value="html">
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
