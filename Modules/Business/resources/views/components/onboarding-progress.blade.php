@props(['step'])

@php($steps = [1 => 'بيانات النشاط', 2 => 'الفرع الأول', 3 => 'دعوة الفريق'])
<div class="border-b border-slate-100 px-5 py-7 sm:px-10">
    <div class="mx-auto max-w-3xl text-center">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">لنجهّز نشاطك التجاري</h1>
        <p class="mt-3 text-sm text-slate-500 sm:text-base">بضع خطوات بسيطة لإنشاء متجرك وإعداد نقطة البيع الخاصة بك.</p>
    </div>
    <ol class="mx-auto mt-8 flex max-w-3xl items-start justify-between" aria-label="تقدم إعداد النشاط">
        @foreach ($steps as $number => $label)
            <li
                class="relative flex flex-1 flex-col items-center text-center {{ $number < 3 ? 'after:absolute after:top-5 after:right-1/2 after:-z-0 after:h-0.5 after:w-full after:bg-slate-200' : '' }} {{ $number < $step ? 'after:bg-indigo-600' : '' }}">
                <span @class([
                    'relative z-10 grid h-10 w-10 place-items-center rounded-full border-2 text-sm font-extrabold transition',
                    'border-indigo-600 bg-indigo-600 text-white' => $number <= $step,
                    'border-slate-300 bg-white text-slate-500' => $number > $step,
                ])>
                    @if ($number < $step)
                        <i class="bx bx-check text-xl" aria-hidden="true"></i>
                    @else
                        {{ $number }}
                    @endif
                </span>
                <span @class([
                    'mt-2 text-xs font-bold sm:text-sm',
                    'text-indigo-700' => $number === $step,
                    'text-slate-500' => $number !== $step,
                ])>{{ $label }}</span>
            </li>
        @endforeach
    </ol>
    <p class="mt-5 text-center text-sm text-slate-500">الخطوة {{ $step }} من 3 <span
            class="mx-1 text-slate-300">•</span> <span class="font-bold text-indigo-600">{{ $step * 33 }}%
            مكتمل</span></p>
</div>
