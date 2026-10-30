<x-tenant-app-layout>
    <x-slot name="header">{{ __('Support Inbox') }}</x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Status filter tabs --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tenant.inbox') }}"
                   class="px-4 py-1.5 rounded-full text-xs font-semibold transition
                          {{ ! $filter ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    {{ __('All') }} ({{ array_sum($counts) }})
                </a>
                @foreach (\App\Models\ContactMessage::STATUSES as $s)
                    <a href="{{ route('tenant.inbox', ['status' => $s]) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-semibold transition
                              {{ $filter === $s ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                        {{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})
                    </a>
                @endforeach
            </div>

            @forelse ($messages as $msg)
                @php
                    $statusStyle = [
                        'new' => 'bg-blue-100 text-blue-700',
                        'open' => 'bg-amber-100 text-amber-700',
                        'replied' => 'bg-emerald-100 text-emerald-700',
                        'closed' => 'bg-gray-200 text-gray-500',
                    ][$msg->status] ?? 'bg-gray-100 text-gray-600';
                    $priorityStyle = [
                        'high' => 'text-red-600',
                        'medium' => 'text-amber-600',
                        'low' => 'text-gray-400',
                    ][$msg->priority] ?? 'text-gray-400';
                @endphp
                <div class="bg-white border border-gray-200 rounded-2xl p-5 {{ $msg->isResolved() ? 'opacity-75' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 flex items-center gap-2 flex-wrap">
                                <span>{{ $msg->name }}</span>
                                <span class="font-normal text-gray-400 text-sm">&lt;{{ $msg->email }}&gt;</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $statusStyle }}">
                                    {{ $msg->status }}
                                </span>
                                <span class="text-[10px] font-bold uppercase {{ $priorityStyle }}" title="Priority">
                                    ● {{ $msg->priority }}
                                </span>
                            </div>
                            @if ($msg->subject)
                                <div class="text-sm font-medium text-indigo-600 mt-0.5">{{ $msg->subject }}</div>
                            @endif
                            @if ($msg->assignee)
                                <div class="text-[11px] text-gray-400 mt-1">
                                    {{ __('Assigned to') }} <span class="font-medium text-gray-600">{{ $msg->assignee->name }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 whitespace-nowrap">{{ $msg->created_at->diffForHumans() }}</div>
                    </div>

                    <p class="text-sm text-gray-600 mt-3 whitespace-pre-wrap">{{ $msg->message }}</p>

                    {{-- Ticket controls --}}
                    <form method="POST" action="{{ route('tenant.inbox.update', $msg) }}"
                          class="flex flex-wrap items-center gap-3 mt-4 pt-3 border-t border-gray-100">
                        @csrf @method('PATCH')
                        <select name="status" class="text-xs border-gray-300 rounded-lg py-1.5">
                            @foreach (\App\Models\ContactMessage::STATUSES as $s)
                                <option value="{{ $s }}" @selected($msg->status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <select name="priority" class="text-xs border-gray-300 rounded-lg py-1.5">
                            @foreach (\App\Models\ContactMessage::PRIORITIES as $p)
                                <option value="{{ $p }}" @selected($msg->priority === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                        <select name="assigned_to" class="text-xs border-gray-300 rounded-lg py-1.5">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" @selected($msg->assigned_to === $agent->id)>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <button class="text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg transition">
                            {{ __('Update') }}
                        </button>
                    </form>

                    <div class="flex items-center gap-4 mt-3">
                        <a href="mailto:{{ $msg->email }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Reply via email →</a>
                        <form method="POST" action="{{ route('tenant.inbox.destroy', $msg) }}"
                              onsubmit="return confirm('{{ __('Delete this ticket permanently?') }}')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-medium text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                        </form>
                        <button x-data="{ draft: null, loading: false }"
                                @click="loading = true; const r = await fetch('{{ route('tenant.inbox.suggest', $msg) }}'); draft = (await r.json()).draft; loading = false"
                                class="text-xs font-medium text-purple-600 hover:text-purple-800">
                            <span x-show="! loading">✨ {{ __('Suggest reply') }}</span>
                            <span x-show="loading">{{ __('Drafting...') }}</span>
                            <template x-if="draft">
                                <div @click.stop class="mt-2 p-3 bg-purple-50 border border-purple-200 rounded-lg text-left">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[10px] font-bold text-purple-500 uppercase">{{ __('AI draft') }}</span>
                                        <button @click="navigator.clipboard.writeText(draft); draft = null" class="text-[10px] text-purple-600 underline">{{ __('copy') }}</button>
                                    </div>
                                    <p class="text-xs text-gray-700 mt-1 whitespace-pre-wrap" x-text="draft"></p>
                                </div>
                            </template>
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center text-gray-400">
                    {{ __('No tickets here. Messages from your public contact form will appear as tickets.') }}
                </div>
            @endforelse
            {{ $messages->links() }}
        </div>
    </div>
</x-tenant-app-layout>
