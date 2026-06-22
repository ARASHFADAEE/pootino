<form method="GET" class="space-y-3 text-sm">
    <select name="current_province_id" data-searchable class="w-full rounded-xl border-slate-300">
        <option value="">استان محل فعلی</option>
        @foreach($provinces as $p)
            <option value="{{ $p->id }}" @selected(($filters['current_province_id'] ?? '') == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>

    <select name="desired_province_id" data-searchable class="w-full rounded-xl border-slate-300">
        <option value="">استان مقصد</option>
        @foreach($provinces as $p)
            <option value="{{ $p->id }}" @selected(($filters['desired_province_id'] ?? '') == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>

    <select name="rank_id" data-searchable class="w-full rounded-xl border-slate-300">
        <option value="">درجه نظامی</option>
        @foreach($ranks as $rank)
            <option value="{{ $rank->id }}" @selected(($filters['rank_id'] ?? '') == $rank->id)>{{ $rank->name }}</option>
        @endforeach
    </select>

    <button class="w-full rounded-xl bg-[var(--color-primary-700)] py-2.5 font-semibold text-white">اعمال فیلتر</button>
    <a href="{{ route('ads.index') }}" class="block w-full rounded-xl border border-slate-300 py-2.5 text-center">پاک کردن</a>
</form>
