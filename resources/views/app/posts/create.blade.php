<x-tenant-app-layout>
    <x-slot name="header">{{ __('Create Post') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('tenant.posts.store') }}" enctype="multipart/form-data">
                @csrf
                @include('app.posts.partials.form', ['post' => null])
            </form>
        </div>
    </div>
</x-tenant-app-layout>
