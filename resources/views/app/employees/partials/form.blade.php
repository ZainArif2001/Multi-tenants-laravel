<div class="grid lg:grid-cols-3 gap-6">
    <!-- Left: personal + job info -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800">{{ __('Personal Information') }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="first_name" :value="__('First Name')" />
                    <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name"
                        :value="old('first_name', $employee?->first_name)" required autofocus />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="last_name" :value="__('Last Name')" />
                    <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name"
                        :value="old('last_name', $employee?->last_name)" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                        :value="old('email', $employee?->email)" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone"
                        :value="old('phone', $employee?->phone)" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <h3 class="font-semibold text-gray-800">{{ __('Job Information') }}</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="job_title" :value="__('Job Title')" />
                    <x-text-input id="job_title" class="block mt-1 w-full" type="text" name="job_title"
                        :value="old('job_title', $employee?->job_title)" />
                    <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="job_role_id" :value="__('Role')" />
                    <select id="job_role_id" name="job_role_id"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($jobRoles as $role)
                            <option value="{{ $role->id }}" @selected(old('job_role_id', $employee?->job_role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="department_id" :value="__('Department')" />
                    <select id="department_id" name="department_id"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $employee?->department_id) == $dept->id)>
                                {{ $dept->name }}{{ $dept->location ? " ({$dept->location})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="manager_id" :value="__('Manager')" />
                    <select id="manager_id" name="manager_id"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_id', $employee?->manager_id) == $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="salary" :value="__('Salary')" />
                    <x-text-input id="salary" class="block mt-1 w-full" type="number" name="salary" step="0.01" min="0"
                        :value="old('salary', $employee?->salary)" />
                    <x-input-error :messages="$errors->get('salary')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="hire_date" :value="__('Hire Date')" />
                    <x-text-input id="hire_date" class="block mt-1 w-full" type="date" name="hire_date"
                        :value="old('hire_date', $employee?->hire_date?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('hire_date')" class="mt-2" />
                </div>
            </div>
        </div>
    </div>

    <!-- Right: project assignment -->
    <div class="space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3">
            <h3 class="font-semibold text-gray-800">{{ __('Assign to Projects') }}</h3>
            @php($assigned = $employee?->projects->keyBy('id') ?? collect())
            @forelse ($projects as $project)
                <div class="flex items-center gap-3 py-1.5">
                    <input type="checkbox" id="proj_{{ $project->id }}"
                        name="projects[{{ $project->id }}][id]" value="{{ $project->id }}"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        @checked($assigned->has($project->id))>
                    <input type="hidden" name="projects[{{ $project->id }}][selected]" value="1">
                    <label for="proj_{{ $project->id }}" class="flex-1 text-sm text-gray-700">{{ $project->name }}</label>
                    <input type="number" name="projects[{{ $project->id }}][hours]" min="0" max="9999"
                        value="{{ $assigned[$project->id]->pivot->assigned_hours ?? '' }}" placeholder="hrs"
                        class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" title="Assigned hours">
                </div>
            @empty
                <p class="text-xs text-gray-500">{{ __('No projects available.') }}</p>
            @endforelse
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <x-primary-button class="w-full justify-center">
                {{ $employee ? __('Update Employee') : __('Save Employee') }}
            </x-primary-button>
        </div>
    </div>
</div>
