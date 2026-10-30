<x-tenant-app-layout>
    <x-slot name="header">{{ __('Leave Requests') }}</x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3">{{ session('success') }}</div>
            @endif

            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-semibold text-gray-900">{{ __('Request leave') }}</h3>
                <form method="POST" action="{{ route('tenant.leaves.store') }}" class="mt-4 grid sm:grid-cols-5 gap-3 items-end">
                    @csrf
                    <select name="type" class="border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="start_date" required class="border-gray-300 rounded-lg text-sm">
                    <input type="date" name="end_date" required class="border-gray-300 rounded-lg text-sm">
                    <input name="reason" placeholder="{{ __('Reason (optional)') }}" class="border-gray-300 rounded-lg text-sm">
                    <button class="px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700">{{ __('Submit') }}</button>
                </form>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl divide-y divide-gray-100">
                @forelse ($leaves as $leave)
                    <div class="flex items-center gap-4 px-6 py-4" x-data="{ editing: false }">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                @if ($isAdmin) {{ $leave->user->name }} · @endif
                                {{ $types[$leave->type] ?? $leave->type }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $leave->start_date->format('M j') }} → {{ $leave->end_date->format('M j') }}
                                ({{ $leave->start_date->diffInDays($leave->end_date) + 1 }} {{ __('days') }})
                                @if ($leave->reason) · {{ $leave->reason }} @endif
                            </div>
                            @if ($leave->admin_note)
                                <div class="text-xs text-gray-400 mt-0.5">{{ __('Note') }}: {{ $leave->admin_note }}</div>
                            @endif
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            {{ ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'][$leave->status] }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                        @if ($isAdmin && $leave->status === 'pending')
                            <form method="POST" action="{{ route('tenant.leaves.review', $leave) }}">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button class="text-xs font-medium text-green-600 hover:text-green-800">{{ __('Approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('tenant.leaves.review', $leave) }}">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button class="text-xs font-medium text-red-600 hover:text-red-800">{{ __('Reject') }}</button>
                            </form>
                        @endif
                        @if ($isAdmin || ($leave->user_id === auth()->id() && $leave->status === 'pending'))
                            <form method="POST" action="{{ route('tenant.leaves.destroy', $leave) }}"
                                  onsubmit="return confirm('{{ __('Remove this leave request?') }}')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-medium text-red-500 hover:text-red-700">
                                    {{ $isAdmin ? __('Delete') : __('Cancel') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-gray-400">{{ __('No leave requests yet.') }}</p>
                @endforelse
            </div>
            {{ $leaves->links() }}
        </div>
    </div>
</x-tenant-app-layout>
