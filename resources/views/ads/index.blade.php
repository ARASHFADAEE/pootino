@extends('layouts.app')

@section('title', 'آگهی‌های تبادل')

@section('content')
<section class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ filtersOpen: false }">
    <div class="mb-6 rounded-2xl bg-gradient-to-l from-[var(--color-primary-700)] to-[var(--color-primary-900)] p-5 text-white sm:p-7">
        <h1 class="text-xl font-extrabold sm:text-2xl">تبادل محل خدمت، ساده و سریع</h1>
        <p class="mt-2 text-sm text-slate-100">{{ $totalActive }} آگهی فعال از سراسر کشور</p>
        <form class="mt-4" method="GET" x-data="{ t: null }">
            <input
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                class="w-full rounded-xl border-0 bg-white px-4 py-3 text-sm text-slate-800"
                placeholder="جستجو بر اساس عنوان یا توضیحات"
                @input="clearTimeout(t); t = setTimeout(() => $el.form.submit(), 350)"
            />
        </form>
    </div>

    <div class="mb-4 md:hidden">
        <button @click="filtersOpen = true" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold">فیلترها</button>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
        <aside class="hidden rounded-2xl border border-slate-200 bg-white p-4 lg:block">
            @include('ads.partials.filters')
        </aside>

        <div class="lg:col-span-3">
            @if($ads->count())
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($ads as $ad)
                        @include('components.ad-card', ['ad' => $ad])
                    @endforeach
                </div>
                <div class="mt-6">{{ $ads->links() }}</div>
            @else
                <x-empty-state>نتیجه‌ای برای فیلتر فعلی پیدا نشد. فیلترها را تغییر بده.</x-empty-state>
            @endif
        </div>
    </div>

    <div x-show="filtersOpen" class="fixed inset-0 z-50 bg-black/40 p-4 lg:hidden" x-transition>
        <div @click.outside="filtersOpen = false" class="mt-12 rounded-2xl bg-white p-4">
            @include('ads.partials.filters')
            <button @click="filtersOpen = false" class="mt-3 w-full rounded-xl border border-slate-300 py-2 text-sm">بستن</button>
        </div>
    </div>
</section>
@endsection
