@extends('layouts.auth')
@section('title', 'ورود | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-accent-100)] px-4 py-8">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <img src="{{ asset('img/Untitled.png') }}" alt="پوتینو" class="mx-auto mb-4 h-12 w-auto" />
        <p class="mb-6 text-center text-sm text-slate-500">شماره موبایل را وارد کن تا کد تایید ارسال شود.</p>

        <form method="POST" action="{{ route('auth.otp.send') }}" class="space-y-4">
            @csrf
            <div x-data="persianNumericInput(@js(old('phone', '')), 11)">
                <label class="mb-1 block text-sm">شماره موبایل</label>
                <input
                    type="text"
                    inputmode="numeric"
                    dir="ltr"
                    x-model="display"
                    @input="onInput()"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)]"
                    placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                />
                <input type="hidden" name="phone" x-ref="hidden" :value="latin">
            </div>
            <button class="w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white">ارسال کد تایید</button>
                    @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
