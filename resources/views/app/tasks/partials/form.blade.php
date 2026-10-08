<div>
    <x-input-label for="title" :value="__('Title')" />
    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
        :value="old('title', $task?->title)" required autofocus />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="project_id" :value="__('Project')" />
    <select id="project_id" name="project_id" required
        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="">{{ __('Select a project') }}</option>
        @foreach ($projects as $id => $name)
            <option value="{{ $id }}" @selected(old('project_id', $task?->project_id) == $id)>{{ $name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="assigned_to" :value="__('Assign To')" />
    <select id="assigned_to" name="assigned_to"
        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="">{{ __('Unassigned') }}</option>
        @foreach ($users as $id => $name)
            <option value="{{ $id }}" @selected(old('assigned_to', $task?->assigned_to) == $id)>{{ $name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="4"
        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $task?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" required
        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        @foreach (['todo' => 'Todo', 'in_progress' => 'In Progress', 'done' => 'Done'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $task?->status ?? 'todo') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="due_date" :value="__('Due Date')" />
    <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date"
        :value="old('due_date', $task?->due_date?->format('Y-m-d'))" />
    <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
</div>
