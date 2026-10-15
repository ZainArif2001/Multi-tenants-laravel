<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form method="post" action="{{ route('tenant.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div x-data="{ preview: '{{ $user->avatar_url }}' }">
            <x-input-label for="avatar" :value="__('Avatar')" />
            <div class="mt-2 flex items-center gap-4">
                <template x-if="preview">
                    <img :src="preview" class="w-14 h-14 rounded-full object-cover border border-gray-200" alt="avatar">
                </template>
                <template x-if="! preview">
                    <div class="w-14 h-14 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xl">{{ $user->initial }}</div>
                </template>
                <input id="avatar" name="avatar" type="file" accept="image/*" class="text-sm text-gray-600"
                       @change="preview = URL.createObjectURL($event.target.files[0])">
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
