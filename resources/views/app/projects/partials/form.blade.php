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

<div class="mt-4 grid sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="start_date" :value="__('Start Date')" />
        <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date"
            :value="old('start_date', $project?->start_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="end_date" :value="__('End Date')" />
        <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date"
            :value="old('end_date', $project?->end_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
    </div>
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
