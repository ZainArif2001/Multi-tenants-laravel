<x-tenant-app-layout>
    <x-slot name="header">{{ __('Roles & Permissions') }}</x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3">{{ session('success') }}</div>
            @endif

            {{-- New role --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6" x-data="{ open: false }">
                <button @click="open = ! open" class="font-semibold text-indigo-600 text-sm">
                    <span x-text="open ? '− Cancel' : '+ Create a custom role'"></span>
                </button>
                <form x-show="open" x-cloak method="POST" action="{{ route('tenant.roles.store') }}" class="mt-4">
                    @csrf
                    <input name="name" required placeholder="{{ __('Role name (lowercase, e.g. editor)') }}"
                           class="w-64 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                    <div class="mt-3 grid sm:grid-cols-3 gap-3">
                        @foreach ($permissions as $group => $perms)
                            <div class="border border-gray-100 rounded-lg p-3">
                                <div class="text-xs font-bold text-gray-500 uppercase mb-2">{{ $group }}</div>
                                @foreach ($perms as $perm)
                                    <label class="flex items-center gap-2 text-xs text-gray-600 py-0.5">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="rounded border-gray-300 text-indigo-600">
                                        {{ explode('.', $perm->name)[1] }}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <button class="mt-4 px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700">{{ __('Create role') }}</button>
                </form>
            </div>

            {{-- Existing roles --}}
            @foreach ($roles as $role)
                <div class="bg-white border border-gray-200 rounded-2xl p-6" x-data="{ editing: false }">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-gray-900">{{ $role->name }}</span>
                            <span class="ml-2 text-xs text-gray-400">{{ $role->users->count() }} {{ __('users') }}</span>
                            @if ($role->name === 'admin')
                                <span class="ml-2 text-[10px] font-bold text-purple-600 uppercase">{{ __('Full access') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($role->name !== 'admin')
                                <button @click="editing = ! editing" class="text-xs text-indigo-600 hover:text-indigo-800">
                                    <span x-text="editing ? '{{ __('Done') }}' : '{{ __('Edit permissions') }}'"></span>
                                </button>
                                @unless (in_array($role->name, ['admin', 'member']))
                                    <form method="POST" action="{{ route('tenant.roles.destroy', $role) }}"
                                          onsubmit="return confirm('{{ __('Delete this role?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </form>
                                @endunless
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.roles.update', $role) }}">
                        @csrf @method('PATCH')
                        <div class="mt-4 grid sm:grid-cols-3 gap-3" :class="{ 'opacity-50 pointer-events-none': ! editing }">
                            @foreach ($permissions as $group => $perms)
                                <div class="border border-gray-100 rounded-lg p-3">
                                    <div class="text-xs font-bold text-gray-500 uppercase mb-2">{{ $group }}</div>
                                    @foreach ($perms as $perm)
                                        <label class="flex items-center gap-2 text-xs text-gray-600 py-0.5">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                                   class="rounded border-gray-300 text-indigo-600"
                                                   :disabled="! editing"
                                                   {{ $role->name === 'admin' || $role->permissions->contains('name', $perm->name) ? 'checked' : '' }}>
                                            {{ explode('.', $perm->name)[1] }}
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <button x-show="editing" x-cloak class="mt-4 px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700">
                            {{ __('Save permissions') }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-tenant-app-layout>
