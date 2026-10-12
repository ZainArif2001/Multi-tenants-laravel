<x-tenant-site-layout>
    <!-- Page header -->
    <section class="bg-slate-900 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold text-white">{{ __('Our Team') }}</h1>
            <p class="mt-3 text-slate-400 max-w-2xl mx-auto">
                {{ __('Meet the people behind :name — the team that makes it all happen.', ['name' => tenant('name')]) }}
            </p>
        </div>
    </section>

    <section class="py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse ($employees as $employee)
                    <div class="group bg-white border border-gray-200 rounded-2xl p-7 text-center hover:shadow-xl hover:-translate-y-1 transition duration-200">
                        <!-- Avatar -->
                        <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-indigo-200">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>

                        <h3 class="mt-4 font-bold text-gray-900 text-lg">{{ $employee->name }}</h3>

                        @if ($employee->job_title)
                            <p class="text-sm text-indigo-600 font-medium">{{ $employee->job_title }}</p>
                        @endif

                        <!-- Role + department chips -->
                        <div class="mt-3 flex flex-wrap justify-center gap-2">
                            @if ($employee->jobRole)
                                <span class="text-xs bg-indigo-50 text-indigo-700 rounded-full px-3 py-1">{{ $employee->jobRole->name }}</span>
                            @endif
                            @if ($employee->department)
                                <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-3 py-1">
                                    {{ $employee->department->name }}
                                </span>
                            @endif
                        </div>

                        @if ($employee->department?->location)
                            <p class="mt-2 text-xs text-gray-400 flex items-center justify-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $employee->department->location }}
                            </p>
                        @endif

                        @if ($employee->email)
                            <a href="mailto:{{ $employee->email }}" class="mt-4 inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-indigo-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $employee->email }}
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="col-span-4 text-center py-16">
                        <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4m4 4a4 4 0 00-4 4"/></svg>
                        </div>
                        <p class="mt-4 text-gray-500">{{ __('No team members yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-tenant-site-layout>
