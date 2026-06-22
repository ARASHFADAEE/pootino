@extends('layouts.auth')
@section('title', 'احراز هویت لازم است | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-accent-100)] px-4 py-8">
    <div class="w-full max-w-sm rounded-2xl border border-amber-200 bg-white p-6 text-center shadow-sm">
        <img src="{{ asset('img/Untitled.png') }}" alt="پوتینو" class="mx-auto mb-4 h-12 w-auto" />
        <h1 class="mb-2 text-xl font-extrabold text-slate-900">احراز هویت لازم است</h1>
        <p class="mb-6 text-sm leading-7 text-slate-600">
            برای ثبت آگهی باید ابتدا اطلاعات هویتی خود را تکمیل و احراز هویت کنید.
        </p>

        <div
            x-data="{
                seconds: 5,
                redirectUrl: @js(route('auth.otp.complete-profile-form')),
                init() {
                    const timer = setInterval(() => {
                        this.seconds--;
                        if (this.seconds <= 0) {
                            clearInterval(timer);
                            window.location.href = this.redirectUrl;
                        }
                    }, 1000);
                }
            }"
            class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-4"
        >
            <p class="text-sm text-amber-900">
                انتقال به صفحه احراز هویت تا
                <span class="font-bold" x-text="seconds"></span>
                ثانیه دیگر...
            </p>
            <a
                href="{{ route('auth.otp.complete-profile-form') }}"
                class="mt-4 inline-block w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white"
            >
                ادامه احراز هویت
            </a>
        </div>
    </div>
</div>
@endsection
