<x-tenant-app-layout>
    <x-slot name="header">{{ __('Categories') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            @can('posts.create')
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Add Category') }}</h3>
                    <form method="POST" action="{{ route('tenant.categories.store') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="flex-1 min-w-[200px]">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <x-input-label for="description" :value="__('Description')" />
                            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" />
                        </div>
                        <x-primary-button>{{ __('Add') }}</x-primary-button>
                    </form>
                </div>
            @endcan

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Slug') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Posts') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" x-data="{ editing: null }">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $category->slug }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $category->posts_count }}</td>
                                <td class="px-6 py-4 text-right text-sm space-x-3">
                                    @can('posts.edit')
                                        <button @click="editing = editing === {{ $category->id }} ? null : {{ $category->id }}"
                                                class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</button>
                                    @endcan
                                    @can('posts.delete')
                                        <form method="POST" action="{{ route('tenant.categories.destroy', $category) }}" class="inline"
                                              onsubmit="return confirm('{{ __('Delete this category?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                            <tr x-show="editing === {{ $category->id }}" x-cloak>
                                <td colspan="4" class="px-6 py-4 bg-gray-50">
                                    <form method="POST" action="{{ route('tenant.categories.update', $category) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')
                                        <div class="flex-1 min-w-[200px]">
                                            <x-input-label :value="__('Name')" />
                                            <x-text-input name="name" type="text" class="mt-1 block w-full" :value="$category->name" required />
                                        </div>
                                        <div class="flex-1 min-w-[200px]">
                                            <x-input-label :value="__('Description')" />
                                            <x-text-input name="description" type="text" class="mt-1 block w-full" :value="$category->description" />
                                        </div>
                                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No categories yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
