<x-tenant-app-layout>
    <x-slot name="header">{{ __('Edit Post') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('tenant.posts.update', $post) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('app.posts.partials.form')
            </form>
        </div>
    </div>
</x-tenant-app-layout>
