@extends('tracker::layouts.master')

@php
    $labels = [
        'not_started' => 'لم يبدأ',
        'planned' => 'مخطط',
        'in_progress' => 'قيد التنفيذ',
        'review' => 'مراجعة',
        'done' => 'مكتمل',
        'blocked' => 'متوقف',
    ];
    $percent = fn (float $value): string => number_format($value * 100, 1).'%';
@endphp

@section('content')
    <header>
        <div class="container">
            <p class="muted" style="color:#b9c6d8">لوحة متابعة عامة</p>
            <h1>خطة POS MVP</h1>
            <p style="margin-bottom:0">الحالة الحالية لمراحل ومهام المشروع</p>
        </div>
    </header>

    <main class="container">
        <section class="summary" aria-label="ملخص المشروع">
            <div class="card"><div class="metric">{{ $percent($summary['progress']) }}</div><div class="muted">التقدم العام الموزون</div></div>
            <div class="card"><div class="metric">{{ $summary['phase_count'] }}</div><div class="muted">المراحل</div></div>
            <div class="card"><div class="metric">{{ $summary['task_count'] }}</div><div class="muted">المهام</div></div>
            <div class="card"><div class="metric">{{ $summary['issues']['blocked'] }}</div><div class="muted">المهام المتوقفة</div></div>
            <div class="card"><div class="metric">{{ $summary['issues']['conflicts'] }}</div><div class="muted">التعارضات</div></div>
            <div class="card"><div class="metric">{{ $summary['issues']['problems'] }}</div><div class="muted">المشكلات</div></div>
        </section>

        <p class="muted">
            آخر تحديث: {{ $meta['last_updated_at'] ?: 'لم يبدأ التتبع بعد' }}
            @if ($meta['updated_by']) — بواسطة {{ $meta['updated_by'] }} @endif
        </p>

        <section class="phase-grid" aria-label="المراحل">
            @foreach ($phases as $phase)
                <article class="card">
                    <div class="task-title">
                        <div><h2 style="margin-bottom:4px">{{ $phase['title'] }}</h2><span class="muted">وزن المرحلة: {{ $phase['weight'] }}</span></div>
                        <span class="status status-{{ $phase['status'] }}">{{ $labels[$phase['status']] }}</span>
                    </div>
                    <div class="bar" aria-label="نسبة المرحلة"><span style="width: {{ $phase['progress'] * 100 }}%"></span></div>
                    <p><strong>{{ $percent($phase['progress']) }}</strong> من {{ count($phase['tasks']) }} مهام</p>

                    @if ($phase['notes']) <p class="muted">ملاحظات المرحلة</p><ul>@foreach ($phase['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul>@endif
                    @if ($phase['conflicts']) <p class="issue">تعارضات: {{ implode(' · ', $phase['conflicts']) }}</p>@endif
                    @if ($phase['problems']) <p class="issue">مشكلات: {{ implode(' · ', $phase['problems']) }}</p>@endif

                    <details>
                        <summary>عرض المهام</summary>
                        @foreach ($phase['tasks'] as $task)
                            <div class="task">
                                <div class="task-title">
                                    <strong>{{ $task['title'] }}</strong>
                                    <span class="status status-{{ $task['status'] }}">{{ $labels[$task['status']] }}</span>
                                </div>
                                @if ($task['objective']) <p class="muted" style="margin:8px 0 0">{{ $task['objective'] }}</p>@endif
                                @if ($task['dependencies']) <p class="muted" style="margin:6px 0 0">الاعتماديات: {{ $task['dependencies'] }}</p>@endif
                                @if ($task['notes']) <ul>@foreach ($task['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul>@endif
                                @if ($task['conflicts']) <p class="issue">تعارض: {{ implode(' · ', $task['conflicts']) }}</p>@endif
                                @if ($task['problems']) <p class="issue">مشكلة: {{ implode(' · ', $task['problems']) }}</p>@endif
                                @if ($task['resolutions']) <p class="muted">الحلول: {{ implode(' · ', $task['resolutions']) }}</p>@endif
                                @if ($task['latest_commit']) <p class="muted">آخر commit: {{ $task['latest_commit'] }}</p>@endif
                            </div>
                        @endforeach
                    </details>
                </article>
            @endforeach
        </section>
    </main>
@endsection
