@extends('tracker::layouts.master')

@php
    $statusLabels = [
        'not_started' => 'Not started',
        'planned' => 'Planned',
        'in_progress' => 'In progress',
        'review' => 'In review',
        'done' => 'Done',
        'blocked' => 'Blocked',
    ];
    $statusStyles = [
        'not_started' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'planned' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'in_progress' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'review' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'done' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'blocked' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];
    $statusDots = [
        'not_started' => 'bg-slate-400',
        'planned' => 'bg-violet-500',
        'in_progress' => 'bg-blue-500',
        'review' => 'bg-amber-500',
        'done' => 'bg-emerald-500',
        'blocked' => 'bg-rose-500',
    ];
    $percent = fn (float $value): string => number_format($value * 100, 0).'%';
    $issueTotal = $summary['issues']['conflicts'] + $summary['issues']['problems'];
@endphp

@section('content')
    <div class="min-h-screen lg:flex">
        <aside class="hidden w-72 shrink-0 flex-col border-r border-slate-800 bg-slate-950 text-slate-300 lg:flex">
            <div class="border-b border-slate-800 px-5 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500 text-lg font-bold text-white shadow-lg shadow-indigo-500/20">P</div>
                    <div>
                        <p class="text-sm font-semibold tracking-wide text-white">POS MVP</p>
                        <p class="text-xs text-slate-500">AI agent workspace</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-5">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[.18em] text-slate-600">Workspace</p>
                <nav class="mt-2 space-y-1">
                    <a href="#overview" class="flex items-center gap-3 rounded-lg bg-slate-800 px-3 py-2.5 text-sm font-medium text-white">
                        <span class="text-indigo-400">◆</span> Overview
                    </a>
                    <a href="#board" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-slate-900 hover:text-white">
                        <span>▦</span> Phase board
                    </a>
                    <a href="#issues" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-slate-900 hover:text-white">
                        <span>!</span> Issues <span class="ml-auto rounded-full bg-rose-500/15 px-2 py-0.5 text-xs text-rose-300">{{ $issueTotal }}</span>
                    </a>
                </nav>

                <p class="mt-8 px-3 text-[10px] font-bold uppercase tracking-[.18em] text-slate-600">Phases</p>
                <nav class="mt-2 space-y-1">
                    @foreach ($phases as $index => $phase)
                        <a href="#phase-{{ $phase['id'] }}" class="group flex items-center gap-2 rounded-lg px-3 py-2 text-xs text-slate-400 transition hover:bg-slate-900 hover:text-white">
                            <span class="w-5 font-mono text-[10px] text-slate-600">{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="min-w-0 flex-1 truncate">{{ $phase['title'] }}</span>
                            <span class="font-mono text-[10px] text-slate-600">{{ $percent($phase['progress']) }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="border-t border-slate-800 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-slate-900 p-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500/20 text-xs font-bold text-indigo-300">AI</div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-slate-200">Agent maintained</p>
                        <p class="truncate text-[11px] text-slate-500">Read-only · Git tracked</p>
                    </div>
                    <span class="ml-auto h-2 w-2 rounded-full bg-emerald-400"></span>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-[1800px] px-5 py-5 sm:px-8">
                    <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-center">
                        <div>
                            <div class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-400">
                                <span>Workspace</span><span>/</span><span class="text-slate-600">MVP delivery tracker</span>
                            </div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-950">Engineering progress</h1>
                            <p class="mt-1 text-sm text-slate-500">A live view of what the AI agent has shipped, is working on, and is blocked by.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-medium text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Agent-maintained</span>
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-medium text-slate-600">Read-only</span>
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-mono text-slate-500">Laravel 10 · PHP 8.1</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="mx-auto max-w-[1800px] space-y-6 px-5 py-6 sm:px-8">
                <section id="overview" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div class="rounded-2xl bg-slate-950 p-6 text-white shadow-card sm:p-7">
                        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[.18em] text-slate-500">Weighted delivery progress</p>
                                <div class="mt-3 flex items-baseline gap-3"><span class="text-5xl font-bold tracking-tight">{{ $percent($summary['progress']) }}</span><span class="text-sm text-slate-500">across {{ $summary['phase_count'] }} phases</span></div>
                            </div>
                            <div class="text-left sm:text-right"><p class="text-xs text-slate-500">Last state update</p><p class="mt-1 font-mono text-xs text-slate-300">{{ $meta['last_updated_at'] ?: 'Not started' }}</p></div>
                        </div>
                        <div class="mt-7 h-3 overflow-hidden rounded-full bg-slate-800"><div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-blue-400 to-emerald-400" style="width: {{ $summary['progress'] * 100 }}%"></div></div>
                        <div class="mt-3 flex justify-between text-[11px] text-slate-500"><span>Project start</span><span>{{ $meta['updated_by'] ? 'Updated by '.$meta['updated_by'] : 'Waiting for first implementation step' }}</span><span>Launch ready</span></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-card"><p class="text-xs text-slate-500">Tasks</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $summary['task_count'] }}</p><p class="mt-1 text-[11px] text-slate-400">across the roadmap</p></div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-card"><p class="text-xs text-slate-500">Blocked</p><p class="mt-2 text-2xl font-bold text-rose-600">{{ $summary['issues']['blocked'] }}</p><p class="mt-1 text-[11px] text-slate-400">needs attention</p></div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-card"><p class="text-xs text-slate-500">Completed</p><p class="mt-2 text-2xl font-bold text-emerald-600">{{ $summary['status_counts']['done'] }}</p><p class="mt-1 text-[11px] text-slate-400">verified tasks</p></div>
                        <div id="issues" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-card"><p class="text-xs text-slate-500">Open issues</p><p class="mt-2 text-2xl font-bold text-amber-600">{{ $issueTotal }}</p><p class="mt-1 text-[11px] text-slate-400">conflicts + problems</p></div>
                    </div>
                </section>

                <section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div><h2 class="text-lg font-bold text-slate-950">Phase board</h2><p class="mt-1 text-sm text-slate-500">Each column is a delivery phase. Cards are the executable tasks.</p></div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="relative block"><span class="sr-only">Search tasks</span><input id="task-search" type="search" placeholder="Search tasks..." class="w-52 rounded-lg border border-slate-200 bg-white px-3 py-2 pl-9 text-sm outline-none ring-indigo-500 placeholder:text-slate-400 focus:ring-2"><span class="pointer-events-none absolute left-3 top-2.5 text-slate-400">⌕</span></label>
                        <select id="status-filter" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 outline-none ring-indigo-500 focus:ring-2"><option value="all">All statuses</option>@foreach ($statusLabels as $status => $label)<option value="{{ $status }}">{{ $label }}</option>@endforeach</select>
                    </div>
                </section>

                <section id="board" class="-mx-5 overflow-x-auto px-5 pb-5 sm:-mx-8 sm:px-8">
                    <div class="flex min-w-max items-start gap-4">
                        @foreach ($phases as $index => $phase)
                            <article id="phase-{{ $phase['id'] }}" data-phase-column class="w-[330px] shrink-0 rounded-2xl border border-slate-200 bg-slate-200/70 p-3">
                                <div class="mb-3 rounded-xl bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="font-mono text-[10px] font-semibold text-slate-400">PHASE {{ str_pad($index, 2, '0', STR_PAD_LEFT) }} · WEIGHT {{ $phase['weight'] }}</p><h3 class="mt-1 truncate text-sm font-bold text-slate-900">{{ $phase['title'] }}</h3></div><span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $statusDots[$phase['status']] }}"></span></div>
                                    <div class="mt-4 flex items-center gap-3"><div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $phase['progress'] * 100 }}%"></div></div><span class="font-mono text-[11px] font-semibold text-slate-500">{{ $percent($phase['progress']) }}</span></div>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400"><span>{{ count($phase['tasks']) }} tasks</span><span>{{ $statusLabels[$phase['status']] }}</span></div>
                                </div>

                                <div class="space-y-3">
                                    @foreach ($phase['tasks'] as $task)
                                        <article data-task-card data-status="{{ $task['status'] }}" data-search="{{ strtolower($task['id'].' '.$task['title'].' '.($task['objective'] ?? '')) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-card">
                                            <div class="flex items-start justify-between gap-2"><span class="font-mono text-[10px] font-semibold text-slate-400">{{ $task['id'] }}</span><span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-semibold ring-1 {{ $statusStyles[$task['status']] }}">{{ $statusLabels[$task['status']] }}</span></div>
                                            <h4 class="mt-3 text-sm font-semibold leading-5 text-slate-900">{{ $task['title'] }}</h4>
                                            @if ($task['objective']) <p class="mt-2 line-clamp-3 text-xs leading-5 text-slate-500">{{ $task['objective'] }}</p>@endif
                                            <div class="mt-4 flex flex-wrap gap-1.5">
                                                @if ($task['dependencies']) <span class="rounded-md bg-slate-50 px-2 py-1 text-[10px] text-slate-500" title="Dependencies">↳ dependencies</span>@endif
                                                @if ($task['evidence']) <span class="rounded-md bg-blue-50 px-2 py-1 text-[10px] text-blue-600">✓ evidence</span>@endif
                                                @if ($task['latest_commit']) <span class="rounded-md bg-slate-900 px-2 py-1 font-mono text-[10px] text-slate-300">{{ $task['latest_commit'] }}</span>@endif
                                                @if ($task['conflicts']) <span class="rounded-md bg-rose-50 px-2 py-1 text-[10px] text-rose-600">{{ count($task['conflicts']) }} conflict{{ count($task['conflicts']) === 1 ? '' : 's' }}</span>@endif
                                                @if ($task['problems']) <span class="rounded-md bg-amber-50 px-2 py-1 text-[10px] text-amber-700">{{ count($task['problems']) }} problem{{ count($task['problems']) === 1 ? '' : 's' }}</span>@endif
                                            </div>
                                            @if ($task['notes'] || $task['resolutions'])
                                                <details class="mt-3 border-t border-slate-100 pt-3"><summary class="cursor-pointer text-[11px] font-medium text-slate-500">Agent notes</summary><div class="mt-2 space-y-2 text-xs text-slate-500">@foreach (array_merge($task['notes'], $task['resolutions']) as $note)<p>{{ $note }}</p>@endforeach</div></details>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const search = document.getElementById('task-search');
            const filter = document.getElementById('status-filter');
            const cards = [...document.querySelectorAll('[data-task-card]')];
            const applyFilters = () => {
                const query = search.value.trim().toLowerCase();
                const status = filter.value;
                cards.forEach((card) => {
                    const matchesSearch = !query || card.dataset.search.includes(query);
                    const matchesStatus = status === 'all' || card.dataset.status === status;
                    card.classList.toggle('hidden', !(matchesSearch && matchesStatus));
                });
            };
            search.addEventListener('input', applyFilters);
            filter.addEventListener('change', applyFilters);
        })();
    </script>
@endsection
