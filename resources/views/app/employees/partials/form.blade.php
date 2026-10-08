<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
        :value="old('name', $employee?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
        :value="old('email', $employee?->email)" />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone"
        :value="old('phone', $employee?->phone)" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="department" :value="__('Department')" />
    <x-text-input id="department" class="block mt-1 w-full" type="text" name="department"
        :value="old('department', $employee?->department)" />
    <x-input-error :messages="$errors->get('department')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="position" :value="__('Position')" />
    <x-text-input id="position" class="block mt-1 w-full" type="text" name="position"
        :value="old('position', $employee?->position)" />
    <x-input-error :messages="$errors->get('position')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="joined_at" :value="__('Joining Date')" />
    <x-text-input id="joined_at" class="block mt-1 w-full" type="date" name="joined_at"
        :value="old('joined_at', $employee?->joined_at?->format('Y-m-d'))" />
    <x-input-error :messages="$errors->get('joined_at')" class="mt-2" />
</div>
