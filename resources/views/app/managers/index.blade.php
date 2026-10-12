<x-tenant-app-layout>
    <x-slot name="header">{{ __('Managers') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            @can('employees.create')
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Add Manager') }}</h3>
                    <form method="POST" action="{{ route('tenant.managers.store') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="flex-1 min-w-[180px]">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" />
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" />
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Employees') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" x-data="{ editing: null }">
                        @forelse ($managers as $manager)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $manager->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $manager->email ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $manager->phone ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $manager->employees_count }}</td>
                                <td class="px-6 py-4 text-right text-sm space-x-3">
                                    @can('employees.edit')
                                        <button @click="editing = editing === {{ $manager->id }} ? null : {{ $manager->id }}"
                                                class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</button>
                                    @endcan
                                    @can('employees.delete')
                                        <form method="POST" action="{{ route('tenant.managers.destroy', $manager) }}" class="inline"
                                              onsubmit="return confirm('{{ __('Delete this manager?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                            <tr x-show="editing === {{ $manager->id }}" x-cloak>
                                <td colspan="5" class="px-6 py-4 bg-gray-50">
                                    <form method="POST" action="{{ route('tenant.managers.update', $manager) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')
                                        <div class="flex-1 min-w-[160px]">
                                            <x-input-label :value="__('Name')" />
                                            <x-text-input name="name" type="text" class="mt-1 block w-full" :value="$manager->name" required />
                                        </div>
                                        <div class="flex-1 min-w-[160px]">
                                            <x-input-label :value="__('Email')" />
                                            <x-text-input name="email" type="email" class="mt-1 block w-full" :value="$manager->email" />
                                        </div>
                                        <div class="flex-1 min-w-[120px]">
                                            <x-input-label :value="__('Phone')" />
                                            <x-text-input name="phone" type="text" class="mt-1 block w-full" :value="$manager->phone" />
                                        </div>
                                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('No managers yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
