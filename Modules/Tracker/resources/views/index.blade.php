@extends('tracker::layouts.master')

@php
    $statusLabels = ['not_started' => 'Not started', 'planned' => 'Planned', 'in_progress' => 'In progress', 'review' => 'Review', 'done' => 'Done', 'blocked' => 'Blocked'];
    $statusStyles = ['not_started' => 'bg-zinc-800 text-zinc-400', 'planned' => 'bg-violet-500/15 text-violet-300', 'in_progress' => 'bg-blue-500/15 text-blue-300', 'review' => 'bg-amber-500/15 text-amber-300', 'done' => 'bg-emerald-500/15 text-emerald-300', 'blocked' => 'bg-rose-500/15 text-rose-300'];
    $percent = fn (float $value): string => number_format($value * 100, 0).'%';
@endphp

@section('content')
<div class="min-h-screen lg:flex">
    <aside class="hidden w-64 shrink-0 border-r border-zinc-800 bg-[#0d0d0f] lg:flex lg:flex-col">
        <div class="border-b border-zinc-800 p-5"><a href="{{ route('tracker.dashboard') }}" class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 font-bold">P</span><span><b class="block text-sm text-white">POS MVP</b><small class="text-xs text-zinc-500">AI agent workspace</small></span></a></div>
        <nav class="flex-1 space-y-1 p-3 text-sm">
            <a href="{{ route('tracker.dashboard') }}" class="block rounded-lg bg-zinc-800 px-3 py-2.5 font-medium text-white">Overview</a>
            <a href="{{ route('tracker.problems') }}" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-zinc-400 hover:bg-zinc-800 hover:text-white">Problems <span class="rounded-md bg-rose-500/15 px-1.5 py-0.5 text-xs text-rose-300">{{ count($issue_items) }}</span></a>
            <p class="px-3 pb-1 pt-7 text-[10px] font-bold uppercase tracking-widest text-zinc-600">Phases</p>
            @foreach ($phases as $phase)
                <a href="{{ route('tracker.phases.show', $phase['id']) }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-xs text-zinc-400 hover:bg-zinc-800 hover:text-white"><span class="truncate">{{ $phase['title'] }}</span><span class="font-mono text-zinc-600">{{ $percent($phase['progress']) }}</span></a>
            @endforeach
        </nav>
        <div class="border-t border-zinc-800 p-4 text-xs text-zinc-500">English only &middot; Dark mode<br>Git-tracked tracker state</div>
    </aside>

    <main class="min-w-0 flex-1">
        <header class="border-b border-zinc-800 bg-[#0d0d0f]"><div class="mx-auto max-w-7xl px-5 py-5 sm:px-8"><div class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-xs text-zinc-600">Workspace / MVP delivery tracker</p><h1 class="mt-1 text-2xl font-semibold tracking-tight text-white">Engineering progress</h1><p class="mt-1 text-sm text-zinc-500">A focused view of what the agent shipped, what is next, and what needs attention.</p></div><div class="flex gap-2"><a href="{{ route('tracker.problems') }}" class="rounded-lg border border-zinc-700 px-3 py-2 text-sm text-zinc-300 hover:border-rose-400 hover:text-white">Issues <span class="ml-1 text-rose-300">{{ count($issue_items) }}</span></a><span class="rounded-lg bg-emerald-500/10 px-3 py-2 text-xs font-medium text-emerald-300">Agent maintained</span></div></div></div></header>
        <div class="mx-auto max-w-7xl space-y-6 px-5 py-6 sm:px-8">
            @if (session('status'))<div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('status') }}</div>@endif
            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 p-4 xl:col-span-2"><p class="text-xs text-indigo-300">Weighted progress</p><p class="mt-2 text-4xl font-semibold text-white">{{ $percent($summary['progress']) }}</p><div class="mt-4 h-2 rounded-full bg-zinc-800"><div class="h-full rounded-full bg-indigo-500" style="width:{{ $summary['progress'] * 100 }}%"></div></div></div>
                <div class="rounded-xl border border-zinc-800 bg-[#111113] p-4"><p class="text-xs text-zinc-500">Tasks</p><p class="mt-2 text-2xl font-semibold">{{ $summary['task_count'] }}</p><p class="mt-1 text-xs text-zinc-600">{{ $summary['phase_count'] }} phases</p></div>
                <div class="rounded-xl border border-zinc-800 bg-[#111113] p-4"><p class="text-xs text-zinc-500">Completed</p><p class="mt-2 text-2xl font-semibold text-emerald-300">{{ $summary['status_counts']['done'] }}</p><p class="mt-1 text-xs text-zinc-600">verified tasks</p></div>
                <div class="rounded-xl border border-rose-500/20 bg-rose-500/5 p-4"><p class="text-xs text-zinc-500">Needs attention</p><p class="mt-2 text-2xl font-semibold text-rose-300">{{ count($issue_items) + $summary['issues']['blocked'] }}</p><p class="mt-1 text-xs text-zinc-600"><a href="{{ route('tracker.problems') }}" class="hover:text-white">view problems</a></p></div>
            </section>

            <section class="rounded-xl border border-zinc-800 bg-[#111113]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4"><div><h2 class="font-semibold text-white">Phase board</h2><p class="mt-1 text-xs text-zinc-500">Open a phase or task for its full implementation context.</p></div><div class="flex gap-2"><input id="task-search" type="search" placeholder="Filter phases or tasks..." class="w-52 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white outline-none placeholder:text-zinc-600 focus:border-indigo-500"><select id="status-filter" class="rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-300 outline-none focus:border-indigo-500"><option value="all">All statuses</option>@foreach ($statusLabels as $status => $label)<option value="{{ $status }}">{{ $label }}</option>@endforeach</select></div></div>
                <div class="divide-y divide-zinc-800">
                    @foreach ($phases as $phase)
                        <article data-phase-row data-search="{{ strtolower($phase['id'].' '.$phase['title']) }}" class="p-5"><div class="flex flex-wrap items-center gap-4"><div class="min-w-0 flex-1"><a href="{{ route('tracker.phases.show', $phase['id']) }}" class="font-semibold text-white hover:text-indigo-300">{{ $phase['title'] }}</a><p class="mt-1 font-mono text-[11px] text-zinc-600">{{ $phase['id'] }} &middot; weight {{ $phase['weight'] }}</p></div><span class="rounded-md px-2 py-1 text-xs {{ $statusStyles[$phase['status']] }}">{{ $statusLabels[$phase['status']] }}</span><span class="w-12 text-right font-mono text-sm text-zinc-400">{{ $percent($phase['progress']) }}</span></div><div class="mt-3 h-1.5 rounded-full bg-zinc-800"><div class="h-full rounded-full {{ $phase['progress'] === 1.0 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width:{{ $phase['progress'] * 100 }}%"></div></div><div class="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-4">@foreach ($phase['tasks'] as $task)<a data-task-row data-status="{{ $task['status'] }}" data-search="{{ strtolower($task['id'].' '.$task['title'].' '.($task['objective'] ?? '')) }}" href="{{ route('tracker.tasks.show', [$phase['id'], $task['id']]) }}" class="rounded-lg border border-zinc-800 bg-zinc-900/60 p-3 hover:border-indigo-500/60"><div class="flex items-center justify-between gap-2"><span class="truncate font-mono text-[10px] text-zinc-600">{{ $task['id'] }}</span><span class="rounded px-1.5 py-0.5 text-[10px] {{ $statusStyles[$task['status']] }}">{{ $statusLabels[$task['status']] }}</span></div><p class="mt-2 truncate text-sm text-zinc-300">{{ $task['title'] }}</p>@if ($task['problems'] || $task['conflicts'])<p class="mt-2 text-[11px] text-rose-300">{{ count($task['problems']) + count($task['conflicts']) }} issue(s)</p>@endif</a>@endforeach</div></article>
                    @endforeach
                </div>
            </section>
        </div>
    </main>
</div>
<script>
(() => { const search = document.getElementById('task-search'); const filter = document.getElementById('status-filter'); const phases = [...document.querySelectorAll('[data-phase-row]')]; const apply = () => { const q = search.value.toLowerCase().trim(); const s = filter.value; phases.forEach((phase) => { const phaseMatch = !q || phase.dataset.search.includes(q); let visibleTasks = 0; phase.querySelectorAll('[data-task-row]').forEach((task) => { const show = (!q || task.dataset.search.includes(q)) && (s === 'all' || task.dataset.status === s); task.classList.toggle('hidden', !show); if (show) visibleTasks++; }); phase.classList.toggle('hidden', !(phaseMatch || visibleTasks)); }); }; search.addEventListener('input', apply); filter.addEventListener('change', apply); })();
</script>
@endsection
