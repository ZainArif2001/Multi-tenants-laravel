<x-tenant-app-layout>
    <x-slot name="header">{{ __('Edit Employee') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('tenant.employees.update', $employee) }}">
                @csrf
                @method('PUT')
                @include('app.employees.partials.form')
            </form>
        </div>
    </div>
</x-tenant-app-layout>
