@extends('layouts.app')

@section('title', $ad->title)

@section('content')
<section class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ copied: false }">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        <h1 class="text-xl font-extrabold text-slate-900 sm:text-2xl">{{ $ad->title }}</h1>
        <p class="mt-4 text-sm leading-7 text-slate-700">{{ $ad->description ?: 'توضیحی ثبت نشده است.' }}</p>

        <div class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-xl bg-slate-50 p-3">محل فعلی: {{ $ad->currentCity->name }} - {{ $ad->currentProvince->name }}</div>
            <div class="rounded-xl bg-slate-50 p-3">مقصد: {{ $ad->desiredCity->name }} - {{ $ad->desiredProvince->name }}</div>
            <div class="rounded-xl bg-slate-50 p-3">درجه: {{ $ad->rank->name }}</div>
            <div class="rounded-xl bg-slate-50 p-3">تحصیلات: {{ $ad->educationLevel->name }}</div>
        </div>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
            @auth
                <p class="text-xs font-semibold text-slate-500">شماره تماس</p>
                <div class="mt-3 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-2xl font-extrabold tracking-wider text-slate-900 sm:text-right" dir="ltr">{{ $ad->phone }}</p>
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
                    <a href="{{ route('auth.otp.phone') }}" class="mt-3 inline-block rounded-xl bg-[var(--color-primary-700)] px-4 py-2 text-sm font-semibold text-white">ورود / ثبت‌نام</a>
                </div>
            @endauth
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
