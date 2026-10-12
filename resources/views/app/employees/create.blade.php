<x-tenant-app-layout>
    <x-slot name="header">{{ __('Add Employee') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('tenant.employees.store') }}">
                @csrf
                @include('app.employees.partials.form', ['employee' => null])
            </form>
        </div>
    </div>
</x-tenant-app-layout>
