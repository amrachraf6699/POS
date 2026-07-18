@extends('tracker::layouts.master')

@php
    $statusLabels = ['not_started' => 'Not started', 'planned' => 'Planned', 'in_progress' => 'In progress', 'review' => 'Review', 'done' => 'Done', 'blocked' => 'Blocked'];
@endphp

@section('title', $task['title'].' · POS MVP Tracker')
@section('content')
<main class="min-h-screen">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8">
        <div class="flex items-center justify-between gap-4"><a href="{{ route('tracker.phases.show', $phase['id']) }}" class="text-sm text-zinc-500 hover:text-white">← {{ $phase['title'] }}</a><a href="{{ route('tracker.problems') }}" class="text-sm text-rose-300 hover:text-rose-200">Problems</a></div>
        <div class="mt-8 rounded-xl border border-zinc-800 bg-[#111113] p-6 sm:p-8">
            <p class="font-mono text-xs text-zinc-600">{{ $task['id'] }}</p>
            <div class="mt-3 flex flex-wrap items-start justify-between gap-4"><h1 class="text-2xl font-semibold text-white">{{ $task['title'] }}</h1><span class="rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-300">{{ $statusLabels[$task['status']] }}</span></div>
            @if ($task['objective'])<p class="mt-5 text-base leading-7 text-zinc-400">{{ $task['objective'] }}</p>@endif
            <div class="mt-7 grid gap-5 border-y border-zinc-800 py-6 sm:grid-cols-2"><div><h2 class="text-xs font-medium uppercase tracking-wider text-zinc-600">Dependencies</h2><p class="mt-2 text-sm text-zinc-300">{{ is_array($task['dependencies']) ? implode(', ', $task['dependencies']) : ($task['dependencies'] ?: 'None') }}</p></div><div><h2 class="text-xs font-medium uppercase tracking-wider text-zinc-600">Latest commit</h2><p class="mt-2 font-mono text-sm text-zinc-300">{{ $task['latest_commit'] ?: 'Not available' }}</p></div></div>
            @foreach (['notes' => 'Agent notes', 'evidence' => 'Evidence', 'resolutions' => 'Resolutions'] as $field => $label)
                <section class="mt-7"><h2 class="text-sm font-medium text-white">{{ $label }}</h2>@if ($task[$field])<ul class="mt-3 space-y-2">@foreach ($task[$field] as $item)<li class="rounded-lg bg-zinc-900 px-3 py-2 text-sm leading-6 text-zinc-400">{{ $item }}</li>@endforeach</ul>@else<p class="mt-3 text-sm text-zinc-600">None recorded.</p>@endif</section>
            @endforeach
            @if ($task['problems'] || $task['conflicts'])
                <section class="mt-7"><h2 class="text-sm font-medium text-rose-300">Open problems</h2><div class="mt-3 space-y-2">
                    @foreach ($dashboard['issue_items'] as $issue)
                        @if ($issue['task_id'] === $task['id'])<a href="{{ route('tracker.problems.show', $issue['id']) }}" class="block rounded-lg border border-rose-500/20 bg-rose-500/5 px-3 py-2 text-sm text-rose-200 hover:border-rose-400">{{ $issue['message'] }}</a>@endif
                    @endforeach
                </div></section>
            @endif
        </div>
    </div>
</main>
@endsection
