@php
    $editing = isset($ad);
@endphp

<form method="POST" action="{{ $editing ? route('ads.update', $ad) : route('ads.store') }}" class="space-y-4">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm">عنوان آگهی</label>
            <input class="w-full rounded-xl border-slate-300" name="title" value="{{ old('title', $ad->title ?? '') }}" />
        </div>
        <div>
            <label class="mb-1 block text-sm">شماره تماس</label>
            <input class="w-full rounded-xl border-slate-300" dir="ltr" name="phone" value="{{ old('phone', $ad->phone ?? auth()->user()->phone) }}" />
        </div>
        <div>
            <label class="mb-1 block text-sm">استان فعلی</label>
            <select class="w-full rounded-xl border-slate-300" name="current_province_id">
                @foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('current_province_id', $ad->current_province_id ?? '') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm">شهر فعلی (شناسه)</label>
            <input class="w-full rounded-xl border-slate-300" name="current_city_id" value="{{ old('current_city_id', $ad->current_city_id ?? '') }}" />
        </div>
        <div>
            <label class="mb-1 block text-sm">استان مقصد</label>
            <select class="w-full rounded-xl border-slate-300" name="desired_province_id">
                @foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('desired_province_id', $ad->desired_province_id ?? '') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm">شهر مقصد (شناسه)</label>
            <input class="w-full rounded-xl border-slate-300" name="desired_city_id" value="{{ old('desired_city_id', $ad->desired_city_id ?? '') }}" />
        </div>
        <div>
            <label class="mb-1 block text-sm">شاخه نیرو (شناسه)</label>
            <input class="w-full rounded-xl border-slate-300" name="current_branch_id" value="{{ old('current_branch_id', $ad->current_branch_id ?? '') }}" />
        </div>
        <div>
            <label class="mb-1 block text-sm">درجه</label>
            <select class="w-full rounded-xl border-slate-300" name="rank_id">
                @foreach($ranks as $rank)<option value="{{ $rank->id }}" @selected(old('rank_id', $ad->rank_id ?? '') == $rank->id)>{{ $rank->name }}</option>@endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm">تحصیلات</label>
            <select class="w-full rounded-xl border-slate-300" name="education_level_id">
                @foreach($educationLevels as $level)<option value="{{ $level->id }}" @selected(old('education_level_id', $ad->education_level_id ?? '') == $level->id)>{{ $level->name }}</option>@endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm">توضیحات</label>
            <textarea class="w-full rounded-xl border-slate-300" name="description" rows="4">{{ old('description', $ad->description ?? '') }}</textarea>
        </div>
    </div>

    <button class="w-full rounded-xl bg-[var(--color-military-700)] py-3 text-sm font-semibold text-white">{{ $editing ? 'ذخیره تغییرات' : 'ثبت آگهی' }}</button>
</form>
