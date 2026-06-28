@php
    $editing = isset($ad);
@endphp

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <p class="mb-1 font-semibold">لطفاً خطاهای زیر را برطرف کنید:</p>
        <ul class="list-disc space-y-1 pr-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $editing ? route('ads.update', $ad) : route('ads.store') }}" class="space-y-4">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-slate-700">عنوان آگهی <span class="text-red-500">*</span></label>
            <input class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('title') border-red-400 @enderror" name="title" value="{{ old('title', $ad->title ?? '') }}" placeholder="مثال: تبادل محل خدمت تهران به شیراز" required />
            @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-slate-700">شماره تماس</label>
            <input class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-center text-sm text-slate-700" dir="ltr" name="phone" value="{{ old('phone', $ad->phone ?? auth()->user()->phone) }}" readonly />
            <p class="mt-1 text-xs text-slate-500">شماره تماس از حساب کاربری خوانده می‌شود و قابل تغییر نیست.</p>
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">محل خدمت فعلی (استان) <span class="text-red-500">*</span></label>
            <select data-searchable class="w-full rounded-xl border-slate-300 @error('current_province_id') border-red-400 @enderror" name="current_province_id" required>
                <option value="">انتخاب استان</option>
                @foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('current_province_id', $ad->current_province_id ?? '') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
            @error('current_province_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-semibold text-slate-700">محل درخواستی (استان) <span class="text-red-500">*</span></label>
            <select data-searchable class="w-full rounded-xl border-slate-300 @error('desired_province_id') border-red-400 @enderror" name="desired_province_id" required>
                <option value="">انتخاب استان</option>
                @foreach($provinces as $p)<option value="{{ $p->id }}" @selected(old('desired_province_id', $ad->desired_province_id ?? '') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
            @error('desired_province_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-slate-700">ارگان <span class="text-red-500">*</span></label>
            <select class="w-full rounded-xl border-slate-300 @error('branch_type') border-red-400 @enderror" name="branch_type" required>
                <option value="">انتخاب ارگان</option>
                <option value="army" @selected(old('branch_type', $editing ? ($ad->currentBranch->type ?? '') : '') === 'army')>ارتش جمهوری اسلامی</option>
                <option value="sepah" @selected(old('branch_type', $editing ? ($ad->currentBranch->type ?? '') : '') === 'sepah')>سپاه پاسداران</option>
                <option value="police" @selected(old('branch_type', $editing ? ($ad->currentBranch->type ?? '') : '') === 'police')>نیروی انتظامی</option>
            </select>
            @error('branch_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-semibold text-slate-700">توضیحات متقاضی</label>
            <textarea class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('description') border-red-400 @enderror" name="description" rows="4" placeholder="توضیح کوتاه درباره شرایط تبادل...">{{ old('description', $ad->description ?? '') }}</textarea>
            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <button type="submit" class="w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white">{{ $editing ? 'ذخیره تغییرات' : 'ثبت آگهی' }}</button>
</form>
