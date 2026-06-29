@if($ads->count())
    <div
        x-data="infiniteAds(@js($ads->nextPageUrl()))"
        x-init="init()"
    >
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @include('ads.partials.cards')
        </div>

        <div x-show="hasMore" x-ref="sentinel" class="flex flex-col items-center justify-center py-8">
            <div x-show="loading" class="flex items-center gap-2 text-sm text-slate-500">
                <span class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-[var(--color-primary-700)]"></span>
                <span>در حال بارگذاری...</span>
            </div>
        </div>

        <p x-show="!hasMore && !loading" class="py-6 text-center text-xs text-slate-400">همه آگهی‌ها نمایش داده شد</p>
    </div>
@else
    <x-empty-state>نتیجه‌ای برای فیلتر فعلی پیدا نشد. فیلترها را تغییر بده.</x-empty-state>
@endif
