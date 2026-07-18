@extends('tracker::layouts.master')

@php
    $statusLabels = ['not_started' => 'Not started', 'planned' => 'Planned', 'in_progress' => 'In progress', 'review' => 'Review', 'done' => 'Done', 'blocked' => 'Blocked'];
@endphp

@section('title', $phase['title'].' · POS MVP Tracker')
@section('content')
<main class="min-h-screen">
    <div class="mx-auto max-w-5xl px-5 py-8 sm:px-8">
        <a href="{{ route('tracker.dashboard') }}" class="text-sm text-zinc-500 hover:text-white">← Overview</a>
        <div class="mt-7 flex flex-wrap items-end justify-between gap-5">
            <div><p class="font-mono text-xs text-zinc-600">{{ $phase['id'] }} · weight {{ $phase['weight'] }}</p><h1 class="mt-2 text-3xl font-semibold text-white">{{ $phase['title'] }}</h1><p class="mt-2 text-sm text-zinc-500">{{ $phase['status'] === 'done' ? 'Phase complete.' : 'Phase execution detail and task checklist.' }}</p></div>
            <div class="text-right"><p class="text-3xl font-semibold text-indigo-300">{{ number_format($phase['progress'] * 100, 0) }}%</p><p class="text-xs text-zinc-600">{{ $statusLabels[$phase['status']] }}</p></div>
        </div>
        <div class="mt-8 h-2 rounded-full bg-zinc-800"><div class="h-full rounded-full bg-indigo-500" style="width:{{ $phase['progress'] * 100 }}%"></div></div>
        <div class="mt-8 space-y-3">
            @foreach ($phase['tasks'] as $task)
                <a href="{{ route('tracker.tasks.show', [$phase['id'], $task['id']]) }}" class="block rounded-xl border border-zinc-800 bg-[#111113] p-5 hover:border-indigo-500/60">
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="font-mono text-[11px] text-zinc-600">{{ $task['id'] }}</p><h2 class="mt-2 font-medium text-white">{{ $task['title'] }}</h2></div><span class="rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-300">{{ $statusLabels[$task['status']] }}</span></div>
                    @if ($task['objective'])<p class="mt-3 text-sm leading-6 text-zinc-500">{{ $task['objective'] }}</p>@endif
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-zinc-500">
                        @if ($task['problems'])<span class="text-amber-300">{{ count($task['problems']) }} problem(s)</span>@endif
                        @if ($task['conflicts'])<span class="text-rose-300">{{ count($task['conflicts']) }} conflict(s)</span>@endif
                        @if ($task['latest_commit'])<span class="font-mono">{{ $task['latest_commit'] }}</span>@endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</main>
@endsection
