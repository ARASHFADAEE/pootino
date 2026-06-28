@props([
    'name' => 'birth_date',
    'value' => '',
    'label' => 'تاریخ تولد',
    'required' => true,
])

<div
    x-data="jalaliDatePicker(@js(old($name, $value)))"
    class="relative"
    @keydown.escape.window="open && closePicker()"
>
    <label class="mb-1 block text-sm font-medium text-slate-700">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>

    <input type="hidden" name="{{ $name }}" :value="formatted" @if($required) required @endif>

    <button
        type="button"
        @click="openPicker()"
        class="jdp-trigger @error($name) jdp-trigger--error @enderror"
        :class="display ? 'text-slate-900' : 'text-slate-400'"
        aria-haspopup="dialog"
        :aria-expanded="open"
        aria-label="{{ $label }}"
    >
        <span x-text="display || 'انتخاب تاریخ تولد'"></span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3M4 11h16M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
        </svg>
    </button>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror

    <template x-teleport="body">
        <div x-show="open" x-cloak>
            <div
                class="jdp-backdrop"
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closePicker()"
                aria-hidden="true"
            ></div>

            <div
                x-ref="panel"
                x-show="open"
                x-transition
                :class="isMobile ? 'jdp-panel jdp-panel--mobile' : 'jdp-panel jdp-panel--desktop'"
                role="dialog"
                aria-modal="true"
                :aria-label="stepLabel"
                tabindex="-1"
                @keydown="onPanelKeydown($event)"
            >
                <div class="jdp-handle md:hidden" aria-hidden="true"></div>

                <div class="jdp-header">
                    <p class="jdp-header__title" x-text="stepLabel"></p>
                    <p class="jdp-header__subtitle" x-show="step !== 'year' && pendingYear" x-text="'سال ' + digit(pendingYear)"></p>
                    <div class="jdp-steps" aria-hidden="true">
                        <span class="jdp-step" :class="step === 'year' ? 'jdp-step--active' : (pendingYear ? 'jdp-step--done' : '')"></span>
                        <span class="jdp-step" :class="step === 'month' ? 'jdp-step--active' : (pendingMonth ? 'jdp-step--done' : '')"></span>
                        <span class="jdp-step" :class="step === 'day' ? 'jdp-step--active' : ''"></span>
                    </div>
                </div>

                <div class="jdp-body">
                    {{-- Year --}}
                    <div x-show="step === 'year'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="jdp-decades" role="group" aria-label="پرش سریع بین دهه‌ها">
                            <template x-for="decade in decades" :key="decade">
                                <button
                                    type="button"
                                    class="jdp-decade"
                                    @click="jumpToDecade(decade)"
                                    x-text="digit(decade)"
                                ></button>
                            </template>
                        </div>

                        <div class="jdp-year-input-wrap">
                            <input
                                type="text"
                                inputmode="numeric"
                                class="jdp-year-input"
                                placeholder="یا سال را تایپ کنید (مثال: ۱۳۷۵)"
                                x-model="yearInput"
                                @input="onYearInput()"
                                aria-label="تایپ مستقیم سال تولد"
                                dir="ltr"
                            >
                        </div>

                        <div
                            class="jdp-year-list"
                            x-ref="yearList"
                            @scroll.passive="onYearScroll($event)"
                            role="listbox"
                            aria-label="لیست سال‌ها"
                        >
                            <div class="jdp-year-list__inner" :style="`height: ${yearListHeight}px`">
                                <template x-for="item in visibleYears" :key="item.year">
                                    <button
                                        type="button"
                                        class="jdp-year-item"
                                        :class="isYearFocused(item.year) ? 'jdp-year-item--focused' : ''"
                                        :style="`top: ${item.top}px; height: ${YEAR_ITEM_H}px`"
                                        @click="selectYear(item.year)"
                                        x-text="digit(item.year)"
                                        role="option"
                                        :aria-selected="isYearFocused(item.year)"
                                    ></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Month --}}
                    <div x-show="step === 'month'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <button type="button" class="jdp-back" @click="backToYear()">
                            <span aria-hidden="true">→</span>
                            <span>بازگشت به انتخاب سال</span>
                        </button>
                        <p class="jdp-context" x-text="'سال ' + digit(pendingYear)"></p>
                        <div class="jdp-month-grid" x-ref="monthGrid" role="grid" aria-label="انتخاب ماه">
                            <template x-for="(month, idx) in months" :key="month.index">
                                <button
                                    type="button"
                                    class="jdp-month"
                                    :data-month-focus="idx"
                                    @click="selectMonth(month.index)"
                                    x-text="month.name"
                                    role="gridcell"
                                    :tabindex="monthFocusIndex === idx ? 0 : -1"
                                ></button>
                            </template>
                        </div>
                    </div>

                    {{-- Day --}}
                    <div x-show="step === 'day'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <button type="button" class="jdp-back" @click="backToMonth()">
                            <span aria-hidden="true">→</span>
                            <span>بازگشت به انتخاب ماه</span>
                        </button>
                        <p class="jdp-context" x-text="digit(pendingYear) + ' · ' + months[pendingMonth - 1].name"></p>
                        <div class="jdp-day-grid" x-ref="dayGrid" role="grid" aria-label="انتخاب روز">
                            <template x-for="(cell, idx) in days" :key="cell.day">
                                <button
                                    type="button"
                                    class="jdp-day"
                                    :class="{
                                        'jdp-day--selected': cell.selected,
                                        'jdp-day--focused': dayFocusIndex === idx && !cell.selected,
                                    }"
                                    :data-day-focus="idx"
                                    :disabled="!cell.valid"
                                    @click="selectDay(cell.day)"
                                    x-text="digit(cell.day)"
                                    role="gridcell"
                                    :tabindex="dayFocusIndex === idx ? 0 : -1"
                                    :aria-label="'روز ' + digit(cell.day)"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <p x-show="validationError" x-text="validationError" class="jdp-error" role="alert"></p>
                </div>
            </div>
        </div>
    </template>
</div>
