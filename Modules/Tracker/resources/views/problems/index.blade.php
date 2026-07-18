@extends('tracker::layouts.master')

@php
    $statusStyles = ['not_started' => 'text-zinc-400', 'planned' => 'text-violet-300', 'in_progress' => 'text-blue-300', 'review' => 'text-amber-300', 'done' => 'text-emerald-300', 'blocked' => 'text-rose-300'];
@endphp

@section('title', 'Problems · POS MVP Tracker')
@section('content')
<main class="min-h-screen"><div class="mx-auto max-w-5xl px-5 py-8 sm:px-8"><div class="mb-8 flex flex-wrap items-end justify-between gap-4"><div><a href="{{ route('tracker.dashboard') }}" class="text-xs text-zinc-500 hover:text-white">← Back to overview</a><h1 class="mt-4 text-3xl font-semibold text-white">Problems & conflicts</h1><p class="mt-2 max-w-2xl text-sm text-zinc-500">Every open issue from the committed tracker state. Open one to inspect the source and record a resolution.</p></div><span class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-300">{{ count($issue_items) }} open</span></div>
    @if (count($issue_items) === 0)
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-emerald-300">No open problems or conflicts.</div>
    @else
        <div class="space-y-3">
            @foreach ($issue_items as $issue)
                <a href="{{ route('tracker.problems.show', $issue['id']) }}" class="block rounded-xl border border-zinc-800 bg-[#111113] p-5 transition hover:border-indigo-500/60">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2"><span class="rounded-md {{ $issue['type'] === 'problem' ? 'bg-amber-500/15 text-amber-300' : 'bg-rose-500/15 text-rose-300' }} px-2 py-1 text-[11px] font-medium">{{ $issue['label'] }}</span><span class="font-mono text-[11px] text-zinc-600">{{ $issue['id'] }}</span></div>
                            <h2 class="mt-3 text-base font-medium text-white">{{ $issue['message'] }}</h2>
                        </div>
                        <span class="{{ $statusStyles[$issue['status']] }} text-xs">{{ str_replace('_', ' ', $issue['status']) }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-zinc-500">
                        <span>Phase: <b class="font-normal text-zinc-300">{{ $issue['phase_title'] }}</b></span>
                        @if ($issue['task_title'])
                            <span>Task: <b class="font-normal text-zinc-300">{{ $issue['task_title'] }}</b></span>
                        @endif
                        @if ($issue['latest_commit'])
                            <span class="font-mono">Commit: {{ $issue['latest_commit'] }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div></main>
@endsection
