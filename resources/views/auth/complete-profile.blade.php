@extends('layouts.auth')
@section('title', 'تکمیل ثبت‌نام | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-accent-100)] px-4 py-8">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <img src="{{ asset('img/Untitled.png') }}" alt="پوتینو" class="mx-auto mb-4 h-12 w-auto" />
        <h1 class="mb-1 text-center text-xl font-extrabold">تکمیل اطلاعات</h1>
        <p class="mb-6 text-center text-sm text-slate-500">اطلاعات هویتی خود را وارد کن تا احراز هویت انجام شود.</p>

        <form method="POST" action="{{ route('auth.otp.complete-profile') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نام <span class="text-red-500">*</span></label>
                <input name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('first_name') border-red-400 @enderror" placeholder="مثال: حسین" />
                @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نام خانوادگی <span class="text-red-500">*</span></label>
                <input name="family" value="{{ old('family') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('family') border-red-400 @enderror" placeholder="مثال: فدائی جزی" />
                @error('family')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نام پدر <span class="text-red-500">*</span></label>
                <input name="father_name" value="{{ old('father_name') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('father_name') border-red-400 @enderror" placeholder="مثال: محمود" />
                @error('father_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">کد ملی <span class="text-red-500">*</span></label>
                <input dir="ltr" name="national_code" maxlength="10" value="{{ old('national_code') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('national_code') border-red-400 @enderror" placeholder="0012345678" />
                @error('national_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div x-data="jalaliDatePicker(@js(old('birth_date', '')))" @click.outside="open = false" class="relative">
                <label class="mb-1 block text-sm font-medium text-slate-700">تاریخ تولد <span class="text-red-500">*</span></label>
                <input type="hidden" name="birth_date" :value="formatted" required>
                <button
                    type="button"
                    @click="toggle()"
                    class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm @error('birth_date') border-red-400 @enderror"
                >
                    <span x-text="display || 'انتخاب تاریخ تولد'" :class="display ? 'text-slate-900' : 'text-slate-400'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3M4 11h16M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    </svg>
                </button>
                @error('birth_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <div
                    x-show="open"
                    x-transition
                    class="absolute inset-x-0 top-full z-20 mt-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <button type="button" @click="prevMonth()" class="rounded-lg px-2 py-1 text-slate-600 hover:bg-slate-100">‹</button>
                        <span class="text-sm font-semibold text-slate-800" x-text="monthLabel"></span>
                        <button type="button" @click="nextMonth()" class="rounded-lg px-2 py-1 text-slate-600 hover:bg-slate-100">›</button>
                    </div>
                    <div class="mb-1 grid grid-cols-7 gap-1 text-center text-xs text-slate-400">
                        <template x-for="day in weekdays" :key="day">
                            <span x-text="day"></span>
                        </template>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <template x-for="cell in days" :key="cell.key">
                            <button
                                type="button"
                                @click="!cell.empty && selectDay(cell.day)"
                                :disabled="cell.empty"
                                class="rounded-lg py-1.5 text-sm transition"
                                :class="cell.empty
                                    ? 'invisible pointer-events-none'
                                    : (cell.selected ? 'bg-[var(--color-primary-700)] text-white' : 'text-slate-700 hover:bg-slate-100')"
                                x-text="cell.empty ? '' : cell.day"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white">تکمیل ثبت‌نام</button>
        </form>
    </div>
</div>
@endsection
