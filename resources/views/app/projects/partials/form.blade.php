<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
        :value="old('name', $project?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="4"
        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $project?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" required
        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        @foreach (['active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $project?->status ?? 'active') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>
