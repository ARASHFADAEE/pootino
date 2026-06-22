@php
    $editing = isset($ad);
@endphp

<form method="POST" action="{{ $editing ? route('ads.update', $ad) : route('ads.store') }}" class="space-y-4">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">عنوان آگهی</label>
            <input class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)]" name="title" value="{{ old('title', $ad->title ?? '') }}" placeholder="مثال: تبادل محل خدمت تهران به شیراز" />
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">شماره تماس</label>
            <input class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-center text-sm text-slate-700" dir="ltr" name="phone" value="{{ old('phone', $ad->phone ?? auth()->user()->phone) }}" readonly />
            <p class="mt-1 text-xs text-slate-500">شماره تماس از حساب کاربری خوانده می‌شود و قابل تغییر نیست.</p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">استان فعلی</label>
            <select data-searchable class="w-full rounded-xl border-slate-300" name="current_province_id">
                @foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('current_province_id', $ad->current_province_id ?? '') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">استان مقصد</label>
            <select data-searchable class="w-full rounded-xl border-slate-300" name="desired_province_id">
                @foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('desired_province_id', $ad->desired_province_id ?? '') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">نیروی نظامی</label>
            <select class="w-full rounded-xl border-slate-300" name="branch_type">
                <option value="army" @selected(old('branch_type', $editing ? ($ad->currentBranch->type ?? '') : '') === 'army')>ارتش جمهوری اسلامی</option>
                <option value="sepah" @selected(old('branch_type', $editing ? ($ad->currentBranch->type ?? '') : '') === 'sepah')>سپاه پاسداران</option>
                <option value="police" @selected(old('branch_type', $editing ? ($ad->currentBranch->type ?? '') : '') === 'police')>نیروی انتظامی</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">نام یگان</label>
            <input class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)]" name="unit_name" value="{{ old('unit_name', $editing ? ($ad->unit_name ?? $ad->currentBranch->name ?? '') : '') }}" placeholder="مثال: یگان ۶۵ نوهد" />
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">درجه</label>
            <select data-searchable class="w-full rounded-xl border-slate-300" name="rank_id">
                @foreach($ranks as $rank)<option value="{{ $rank->id }}" @selected(old('rank_id', $ad->rank_id ?? '') == $rank->id)>{{ $rank->name }}</option>@endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-slate-700">تحصیلات</label>
            <select data-searchable class="w-full rounded-xl border-slate-300" name="education_level_id">
                @foreach($educationLevels as $level)<option value="{{ $level->id }}" @selected(old('education_level_id', $ad->education_level_id ?? '') == $level->id)>{{ $level->name }}</option>@endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-slate-700">توضیحات</label>
            <textarea class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)]" name="description" rows="4" placeholder="توضیح کوتاه درباره شرایط تبادل...">{{ old('description', $ad->description ?? '') }}</textarea>
        </div>
    </div>

    <button class="w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white">{{ $editing ? 'ذخیره تغییرات' : 'ثبت آگهی' }}</button>
</form>
