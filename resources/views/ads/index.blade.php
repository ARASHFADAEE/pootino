@extends('layouts.app')

@section('title', 'آگهی‌های تبادل')

@section('content')
<section
    class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
    x-data="liveListing(@js($filters['search'] ?? ''))"
>
    <div class="mb-6 rounded-2xl bg-gradient-to-l from-[var(--color-primary-700)] to-[var(--color-primary-900)] p-5 text-white sm:p-7">
        <h1 class="text-xl font-extrabold sm:text-2xl">تبادل محل خدمت، ساده و سریع</h1>
        <p class="mt-2 text-sm text-slate-100">{{ $totalActive }} آگهی فعال از سراسر کشور</p>
        <div class="relative mt-4">
            <input
                type="search"
                x-model="search"
                @input="onSearchInput()"
                class="w-full rounded-xl border-0 bg-white px-4 py-3 text-sm text-slate-800"
                placeholder="جستجو بر اساس عنوان یا توضیحات"
                autocomplete="off"
            />
            <div
                x-show="loading"
                x-cloak
                class="pointer-events-none absolute inset-y-0 left-3 flex items-center"
            >
                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-[var(--color-primary-700)]"></span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
        <aside class="lg:sticky lg:top-20 lg:col-span-1 lg:self-start">
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <h2 class="mb-4 text-sm font-bold text-slate-900">فیلتر آگهی‌ها</h2>
                @include('ads.partials.filters')
            </div>
        </aside>

        <div class="lg:col-span-3" x-ref="results">
            @include('ads.partials.results')
        </div>
    </div>
</section>
@endsection
