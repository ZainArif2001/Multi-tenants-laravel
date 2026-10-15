<x-tenant-app-layout>
    <x-slot name="header">{{ __('Search') }}</x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('tenant.search') }}" class="mb-6">
                <input name="q" value="{{ $q }}" type="text" placeholder="{{ __('Search posts, employees, projects...') }}" autofocus
                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm px-4 py-3">
            </form>

            @if ($q !== '')
                <div class="bg-white border border-gray-200 rounded-2xl divide-y divide-gray-100">
                    @forelse ($results as $r)
                        <a href="{{ $r['url'] }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50">
                            <span class="text-[10px] font-bold uppercase px-2 py-1 rounded-full bg-indigo-100 text-indigo-700">{{ $r['type'] }}</span>
                            <span class="text-sm text-gray-900">{{ $r['title'] }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-10 text-center text-gray-400">{{ __('No results for') }} "{{ $q }}"</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-tenant-app-layout>
