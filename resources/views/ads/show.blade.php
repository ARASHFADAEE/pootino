@extends('layouts.app')

@section('title', $ad->title)

@section('content')
<section class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ copied: false, reportOpen: false }">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        <h1 class="text-xl font-extrabold text-slate-900 sm:text-2xl">{{ $ad->title }}</h1>

        <div class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-xl bg-slate-50 p-3">محل خدمت فعلی: {{ $ad->currentProvince->name }}</div>
            <div class="rounded-xl bg-slate-50 p-3">محل درخواستی: {{ $ad->desiredProvince->name }}</div>
            @if($ad->currentBranch)
                <div class="rounded-xl bg-slate-50 p-3">ارگان: {{ $ad->currentBranch->typeLabel() }}</div>
            @endif
        </div>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
            <h2 class="mb-2 text-sm font-semibold text-slate-700">توضیحات متقاضی</h2>
            <p class="text-sm leading-7 text-slate-700">{{ $ad->description ?: 'توضیحی ثبت نشده است.' }}</p>
        </div>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
            @auth
                <p class="text-xs font-semibold text-slate-500">شماره تماس</p>
                <div class="mt-3 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-2xl font-extrabold tracking-wider text-slate-900 sm:text-right" dir="ltr">{{ fa_digits($ad->phone) }}</p>
                    <button
                        type="button"
                        class="rounded-xl bg-[var(--color-primary-700)] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[var(--color-primary-900)]"
                        @click="navigator.clipboard.writeText('{{ $ad->phone }}').then(() => { copied = true; setTimeout(() => copied = false, 1800); })"
                    >
                        <span x-show="!copied">کپی شماره</span>
                        <span x-show="copied">کپی شد</span>
                    </button>
                </div>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                    <p class="text-sm font-medium text-amber-900">برای دیدن شماره تماس وارد حساب کاربری شو.</p>
                    <a href="{{ route('auth.otp.phone', ['redirect' => request()->getRequestUri()]) }}" class="mt-3 inline-block rounded-xl bg-[var(--color-primary-700)] px-4 py-2 text-sm font-semibold text-white">ورود / ثبت‌نام</a>
                </div>
            @endauth
        </div>

        @if($ad->approved_at)
            <div class="mt-4 flex items-center gap-2 border-t pt-4 text-sm text-gray-500">
                <span>📅 تاریخ درج آگهی:</span>
                <span class="font-medium text-gray-700">
                    {{ \Morilog\Jalali\Jalalian::fromCarbon($ad->approved_at)->format('l، j F Y') }}
                </span>
                <span class="text-xs text-gray-400">({{ fa_time_ago($ad->approved_at) }})</span>
            </div>
        @endif
    </div>

    <div class="mt-6 flex justify-center">
        @auth
            <button
                type="button"
                @click="reportOpen = true"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-600 transition hover:border-rose-300 hover:text-rose-700"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H8.5l-1 1H5a2 2 0 01-2-2zm9-13.5V9" />
                </svg>
                گزارش آگهی
            </button>
        @else
            <a href="{{ route('auth.otp.phone', ['redirect' => request()->getRequestUri()]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-600 transition hover:border-rose-300 hover:text-rose-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H8.5l-1 1H5a2 2 0 01-2-2zm9-13.5V9" />
                </svg>
                گزارش آگهی
            </a>
        @endauth
    </div>

    <div
        x-show="reportOpen"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @keydown.escape.window="reportOpen = false"
    >
        <div @click.outside="reportOpen = false" class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
            <h3 class="text-base font-bold text-slate-900">گزارش آگهی</h3>
            <p class="mt-1 text-sm text-slate-500">دلیل گزارش را انتخاب کنید.</p>

            <form method="POST" action="{{ route('ads.report', $ad) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">دلیل گزارش</label>
                    <select name="reason" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="fake">اطلاعات نادرست</option>
                        <option value="duplicate">آگهی تکراری</option>
                        <option value="spam">اسپم</option>
                        <option value="other">سایر</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">توضیحات (اختیاری)</label>
                    <textarea name="description" rows="3" maxlength="300" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" placeholder="توضیح کوتاه..."></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="reportOpen = false" class="flex-1 rounded-xl border border-slate-300 py-2.5 text-sm">انصراف</button>
                    <button type="submit" class="flex-1 rounded-xl bg-rose-700 py-2.5 text-sm font-semibold text-white">ثبت گزارش</button>
                </div>
            </form>
        </div>
    </div>

    @if($similarAds->count())
        <div class="mt-8">
            <h2 class="mb-3 text-sm font-bold text-slate-700">آگهی‌های مشابه</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($similarAds as $item)
                    @include('components.ad-card', ['ad' => $item])
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
