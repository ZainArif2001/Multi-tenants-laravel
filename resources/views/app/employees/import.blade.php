<x-tenant-app-layout>
    <x-slot name="header">{{ __('Import Employees') }}</x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">{{ __('Upload Excel or CSV') }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('First row must be headers. Department, role and manager are matched by name — new ones are created automatically. Rows with a duplicate email are skipped.') }}
                </p>

                <form method="POST" action="{{ route('tenant.employees.import') }}" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <label for="file" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-2xl p-10 cursor-pointer hover:border-indigo-400 transition"
                           x-data="{ name: '' }">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span class="mt-2 text-sm text-gray-600" x-text="name || '{{ __('Click to choose file (.xlsx, .xls, .csv)') }}'"></span>
                        <input id="file" type="file" name="file" accept=".csv,.xlsx,.xls" class="hidden" required
                               @change="name = $event.target.files[0]?.name">
                    </label>
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />

                    <div class="mt-4 flex items-center gap-3">
                        <x-primary-button>{{ __('Import') }}</x-primary-button>
                        <a href="{{ route('tenant.employees.template') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                            {{ __('Download template') }}
                        </a>
                        <a href="{{ route('tenant.employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>

            <div class="bg-slate-900 rounded-lg p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">{{ __('Expected columns') }}</p>
                <code class="text-xs text-emerald-400 break-all">first_name, last_name, email, phone, job_title, salary, hire_date, department, role, manager</code>
                <p class="text-xs text-slate-400 mt-2">
                    {{ __('Example row:') }} <code class="text-slate-300">Ali,Khan,ali@example.com,03001234567,Senior Developer,120000,2025-01-15,Engineering,Developer,Zain Arif</code>
                </p>
            </div>
        </div>
    </div>
</x-tenant-app-layout>
