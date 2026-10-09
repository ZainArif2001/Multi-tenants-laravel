<x-tenant-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('tenant.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name', $user->name)" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email', $user->email)" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role" required
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected(old('role', $user->roles->first()?->name) === $role)>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Module Access -->
                        <div class="mt-4">
                            <x-input-label :value="__('Module Access (which projects this user can open)')" />
                            <div class="mt-2 space-y-1">
                                @foreach ($modules as $key => $label)
                                    <label for="module_{{ $key }}" class="inline-flex items-center mr-6">
                                        <input id="module_{{ $key }}" type="checkbox" name="modules[]" value="{{ $key }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(in_array($key, old('modules', $user->allowed_modules ?? [])))>
                                        <span class="ms-2 text-sm text-gray-600">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('modules')" class="mt-2" />
                        </div>

                        <!-- URLs to share with the user -->
                        <div class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">{{ __('Share these URLs with the user') }}</p>
                            <div class="mt-2 space-y-1.5 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-20">{{ __('Login:') }}</span>
                                    <code class="text-indigo-700 bg-white px-2 py-0.5 rounded select-all">{{ $urls['login'] }}</code>
                                </div>
                                @foreach ($modules as $key => $label)
                                    @isset($urls[$key])
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-500 w-20">{{ $label }}:</span>
                                            <code class="text-indigo-700 bg-white px-2 py-0.5 rounded select-all">{{ $urls[$key] }}</code>
                                        </div>
                                    @endisset
                                @endforeach
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password (leave blank to keep current)')" />

                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                autocomplete="new-password" />

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation" autocomplete="new-password" />

                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
