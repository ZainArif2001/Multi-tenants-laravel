<x-tenant-site-layout>
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Our Team') }}</h1>

            <div class="grid md:grid-cols-4 gap-6">
                @forelse ($employees as $employee)
                    <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                        <h3 class="mt-3 font-semibold text-gray-900">{{ $employee->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $employee->position ?? $employee->department }}</p>
                        @if ($employee->email)
                            <p class="mt-1 text-xs text-gray-400">{{ $employee->email }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 col-span-4">{{ __('No team members yet.') }}</p>
                @endforelse
            </div>
        </div>
    </section>
</x-tenant-site-layout>
