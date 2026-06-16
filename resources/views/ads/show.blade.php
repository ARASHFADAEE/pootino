@extends('layouts.app')

@section('title', $ad->title)

@section('content')
<section class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        <h1 class="text-xl font-extrabold text-slate-900 sm:text-2xl">{{ $ad->title }}</h1>
        <p class="mt-4 text-sm leading-7 text-slate-700">{{ $ad->description ?: 'توضیحی ثبت نشده است.' }}</p>

        <div class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-xl bg-slate-50 p-3">محل فعلی: {{ $ad->currentCity->name }} - {{ $ad->currentProvince->name }}</div>
            <div class="rounded-xl bg-slate-50 p-3">مقصد: {{ $ad->desiredCity->name }} - {{ $ad->desiredProvince->name }}</div>
            <div class="rounded-xl bg-slate-50 p-3">درجه: {{ $ad->rank->name }}</div>
            <div class="rounded-xl bg-slate-50 p-3">تحصیلات: {{ $ad->educationLevel->name }}</div>
        </div>

        <div class="mt-5 rounded-xl border border-slate-200 p-4">
            @auth
                <p class="text-xs text-slate-500">شماره تماس</p>
                <p class="mt-1 text-xl font-extrabold tracking-wider" dir="ltr">{{ $ad->phone }}</p>
            @else
                <p class="text-sm text-slate-600">برای دیدن شماره تماس وارد حساب کاربری شو.</p>
                <a href="{{ route('auth.otp.phone') }}" class="mt-2 inline-block rounded-lg bg-[var(--color-military-700)] px-4 py-2 text-sm text-white">ورود / ثبت‌نام</a>
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
