<form method="GET" class="space-y-3 text-sm">
    <input type="hidden" name="search" :value="search.trim()">
    <select name="current_province_id" data-searchable class="w-full rounded-xl border-slate-300">
        <option value="">الان کجا خدمت میکنی؟</option>
        @foreach($provinces as $p)
            <option value="{{ $p->id }}" @selected(($filters['current_province_id'] ?? '') == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>

    <select name="desired_province_id" data-searchable class="w-full rounded-xl border-slate-300">
        <option value="">میخوای کدوم استان بری؟</option>
        @foreach($provinces as $p)
            <option value="{{ $p->id }}" @selected(($filters['desired_province_id'] ?? '') == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>

    <select name="branch_type" class="w-full rounded-xl border-slate-300" style="
    background: #dddddd;
    padding: 10px;
">
        <option value="">ارگان</option>
        <option value="army" @selected(($filters['branch_type'] ?? '') === 'army')>ارتش جمهوری اسلامی</option>
        <option value="sepah" @selected(($filters['branch_type'] ?? '') === 'sepah')>سپاه پاسداران</option>
        <option value="police" @selected(($filters['branch_type'] ?? '') === 'police')>نیروی انتظامی</option>
    </select>

    <button class="w-full rounded-xl bg-[var(--color-primary-700)] py-2.5 font-semibold text-white">جستجو</button>
</form>
