<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Post') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('tenant.posts.update', $post) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                :value="old('title', $post->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="body" :value="__('Body')" />
                            <textarea id="body" name="body" rows="6"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('body', $post->body) }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        @can('posts.publish')
                            <div class="mt-4">
                                <label for="publish" class="inline-flex items-center">
                                    <input id="publish" type="checkbox" name="publish" value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        @checked(old('publish', $post->status === 'published'))>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Published') }}</span>
                                </label>
                            </div>
                        @endcan

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">{{ __('Update') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
