<x-app-layout>
    <x-slot name="header">{{ __('Customization Requests') }}</x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3">{{ session('success') }}</div>
            @endif

            @forelse ($requests as $req)
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">
                                {{ $req->title }}
                                <span class="text-xs font-normal text-indigo-600 ml-1">→ {{ $tenants[$req->tenant_id] ?? $req->tenant_id }}</span>
                                @if ($req->module)
                                    <span class="text-[10px] font-bold uppercase text-gray-400 ml-1">{{ $req->module }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $req->details }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">{{ $req->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0
                            {{ ['pending' => 'bg-amber-100 text-amber-700', 'in_progress' => 'bg-blue-100 text-blue-700', 'done' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'][$req->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('requests.update', $req) }}" class="mt-3 flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="status" class="border-gray-300 rounded-lg text-xs py-1.5">
                            @foreach (['pending', 'in_progress', 'done', 'rejected'] as $s)
                                <option value="{{ $s }}" @selected($req->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                        <input name="admin_note" value="{{ $req->admin_note }}" placeholder="{{ __('Note to tenant (optional)') }}"
                               class="flex-1 border-gray-300 rounded-lg text-xs py-1.5">
                        <button class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700">{{ __('Update') }}</button>
                    </form>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-400">
                    {{ __('No requests yet.') }}
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
