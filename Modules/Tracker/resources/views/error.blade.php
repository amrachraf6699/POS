@extends('tracker::layouts.master')

@section('content')
    <main class="flex min-h-screen items-center justify-center p-6">
        <article class="w-full max-w-lg rounded-2xl border border-rose-200 bg-white p-8 shadow-xl shadow-slate-200/60">
            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-xl text-rose-600">!</div>
            <p class="font-mono text-xs font-semibold uppercase tracking-[.18em] text-rose-500">Tracker configuration error</p>
            <h1 class="mt-3 text-2xl font-bold text-slate-950">Unable to load the board</h1>
            <p class="mt-3 text-sm leading-6 text-slate-500">The agent-maintained tracker state is invalid. Fix <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">tracker/tracker.json</code> and reload this page.</p>
        </article>
    </main>
@endsection
