<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tenants') }}
            <x-button-link href="{{ route('tenants.create') }}" class="ml-4 float-right">
                {{ __('Create Tenant') }}
            </x-button-link>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Name') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Domain Name') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Email') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($tenants as $tenant)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $tenant->name }}
                                        @if ($tenant->suspended)
                                            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600">{{ __('SUSPENDED') }}</span>
                                        @endif
                                        <span class="ml-1 text-[10px] text-gray-400">{{ ucfirst($tenant->plan ?? 'custom') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $tenant->domains->first()->domain ?? $tenant->domain_name }}
                                        @if ($tenant->pending_domain)
                                            <div class="mt-1">
                                                <span class="text-[10px] text-amber-600">⏳ {{ $tenant->pending_domain }}</span>
                                                <form method="POST" action="{{ route('tenants.approve-domain', $tenant) }}" class="inline ml-1">
                                                    @csrf
                                                    <button class="text-[10px] font-bold text-green-600 underline">{{ __('approve') }}</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $tenant->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('tenants.edit', $tenant) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">
                                            {{ __('Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('tenants.impersonate', $tenant) }}" class="inline mr-4">
                                            @csrf
                                            <button type="submit" class="text-purple-600 hover:text-purple-900" title="{{ __('Login as tenant admin') }}">
                                                {{ __('Impersonate') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('tenants.suspend', $tenant) }}" class="inline mr-4">
                                            @csrf
                                            <button type="submit" class="{{ $tenant->suspended ? 'text-green-600 hover:text-green-900' : 'text-amber-600 hover:text-amber-900' }}">
                                                {{ $tenant->suspended ? __('Resume') : __('Suspend') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('tenants.destroy', $tenant) }}" class="inline"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this tenant?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        {{ __('No tenants found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
