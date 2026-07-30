@props(['title', 'description', 'icon' => 'bx-store-alt'])

<div class="relative mx-auto hidden w-full max-w-md flex-col justify-center text-center lg:flex" aria-hidden="true">
    <svg viewBox="0 0 420 300" class="mx-auto w-full max-w-[390px]" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M52 230H370" stroke="#1E2C58" stroke-width="3" stroke-linecap="round" />
        <path d="M89 230V121H246V230" fill="white" stroke="#1E2C58" stroke-width="3" />
        <path d="M75 121H261L245 81H91L75 121Z" fill="#F8FAFC" stroke="#1E2C58" stroke-width="3"
            stroke-linejoin="round" />
        <path d="M87 82H250" stroke="#1E2C58" stroke-width="3" />
        <path d="M89 121H246V150H89V121Z" fill="#D8FAF5" stroke="#1E2C58" stroke-width="3" />
        <path d="M90 121H116V150H90V121ZM142 121H168V150H142V121ZM194 121H220V150H194V121Z" fill="#38BFB2" />
        <path d="M116 121H142V150H116V121ZM168 121H194V150H168V121ZM220 121H246V150H220V121Z" fill="white" />
        <path d="M111 154H151V230H111V154Z" fill="#F8FAFC" stroke="#1E2C58" stroke-width="3" />
        <circle cx="142" cy="194" r="3" fill="#1E2C58" />
        <path d="M171 170H220V230H171V170Z" fill="white" stroke="#1E2C58" stroke-width="3" />
        <path d="M195 170V230M171 199H220" stroke="#1E2C58" stroke-width="2" />
        <path d="M264 70H329V202H264V70Z" fill="white" stroke="#1E2C58" stroke-width="3" />
        <path d="M274 91H319M274 106H309M274 121H314M274 136H300" stroke="#C4CCE3" stroke-width="4"
            stroke-linecap="round" />
        <path d="M275 158H288V171H275V158Z" fill="#D8FAF5" stroke="#38BFB2" stroke-width="2" />
        <path d="M278 164L281 167L286 161" stroke="#167F76" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        <path d="M298 158H319M298 171H314" stroke="#C4CCE3" stroke-width="3" stroke-linecap="round" />
        <path d="M275 181H288V194H275V181Z" fill="#D8FAF5" stroke="#38BFB2" stroke-width="2" />
        <path d="M278 187L281 190L286 184" stroke="#167F76" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        <path d="M298 181H319M298 194H314" stroke="#C4CCE3" stroke-width="3" stroke-linecap="round" />
        <path
            d="M55 80C62 67 78 67 85 80M64 80C69 72 78 72 83 80M331 55C338 43 355 43 363 55M340 55C345 49 354 49 359 55M34 114C39 105 51 105 57 114"
            stroke="#7B89B5" stroke-width="2" stroke-linecap="round" />
        <path d="M55 230C45 214 30 211 19 224M351 230C360 214 377 210 390 224" stroke="#8ECFC7" stroke-width="3"
            stroke-linecap="round" />
    </svg>
    <h2 class="mt-6 text-xl font-extrabold text-slate-800">{{ $title }}</h2>
    <p class="mx-auto mt-3 max-w-sm leading-7 text-sm text-slate-500">{{ $description }}</p>
</div>
