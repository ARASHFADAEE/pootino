import {
    formatJalali,
    getAgeInYears,
    getBirthYearBounds,
    getDecades,
    isValidBirthDate,
    jalaaliMonthLength,
    parseJalali,
    persianMonths,
    toEnDigits,
    toFaDigits,
} from './calendar.js';

const YEAR_ITEM_H = 48;
const YEAR_VIEWPORT_H = 288;
const YEAR_BUFFER = 6;

export function jalaliDatePicker(initialValue = '', options = {}) {
    const parsed = parseJalali(initialValue);
    const bounds = getBirthYearBounds(options.minAge ?? 18, options.maxAge ?? 120);
    const decades = getDecades(bounds.minYear, bounds.maxYear);

    const years = [];
    for (let y = bounds.maxYear; y >= bounds.minYear; y -= 1) {
        years.push(y);
    }

    return {
        open: false,
        step: 'year',
        isMobile: false,
        usePersianDigits: true,
        minAge: options.minAge ?? 18,
        maxAge: options.maxAge ?? 120,
        bounds,
        decades,
        years,
        months: persianMonths.map((name, index) => ({ index: index + 1, name })),
        pendingYear: null,
        pendingMonth: null,
        selectedYear: parsed?.year ?? null,
        selectedMonth: parsed?.month ?? null,
        selectedDay: parsed?.day ?? null,
        yearInput: '',
        yearScrollTop: 0,
        yearFocusIndex: years.indexOf(parsed?.year ?? bounds.defaultYear),
        monthFocusIndex: (parsed?.month ?? 1) - 1,
        dayFocusIndex: (parsed?.day ?? 1) - 1,
        validationError: '',
        YEAR_ITEM_H,
        YEAR_VIEWPORT_H,

        init() {
            this.checkMobile();
            window.addEventListener('resize', () => this.checkMobile());

            if (this.yearFocusIndex < 0) {
                this.yearFocusIndex = years.indexOf(bounds.defaultYear);
            }
        },

        checkMobile() {
            this.isMobile = window.matchMedia('(max-width: 767px)').matches;
        },

        digit(value) {
            return this.usePersianDigits ? toFaDigits(value) : String(value);
        },

        get formatted() {
            if (!this.selectedYear || !this.selectedMonth || !this.selectedDay) {
                return '';
            }

            return formatJalali(this.selectedYear, this.selectedMonth, this.selectedDay);
        },

        get display() {
            if (!this.formatted) {
                return '';
            }

            return `${this.digit(this.selectedDay)} ${persianMonths[this.selectedMonth - 1]} ${this.digit(this.selectedYear)}`;
        },

        get stepLabel() {
            return {
                year: 'انتخاب سال',
                month: 'انتخاب ماه',
                day: 'انتخاب روز',
            }[this.step];
        },

        get days() {
            if (!this.pendingYear || !this.pendingMonth) {
                return [];
            }

            const length = jalaaliMonthLength(this.pendingYear, this.pendingMonth);
            const items = [];

            for (let d = 1; d <= length; d += 1) {
                const valid = isValidBirthDate(this.pendingYear, this.pendingMonth, d, this.minAge, this.maxAge);
                items.push({
                    day: d,
                    valid,
                    selected: this.selectedYear === this.pendingYear
                        && this.selectedMonth === this.pendingMonth
                        && this.selectedDay === d,
                });
            }

            return items;
        },

        get visibleYears() {
            const start = Math.max(0, Math.floor(this.yearScrollTop / YEAR_ITEM_H) - YEAR_BUFFER);
            const visibleCount = Math.ceil(YEAR_VIEWPORT_H / YEAR_ITEM_H) + YEAR_BUFFER * 2;
            const end = Math.min(this.years.length, start + visibleCount);

            return this.years.slice(start, end).map((year, i) => ({
                year,
                top: (start + i) * YEAR_ITEM_H,
            }));
        },

        get yearListHeight() {
            return this.years.length * YEAR_ITEM_H;
        },

        openPicker() {
            this.validationError = '';
            this.open = true;
            this.step = 'year';
            this.pendingYear = null;
            this.pendingMonth = null;
            this.yearInput = '';

            if (this.selectedYear) {
                this.yearFocusIndex = this.years.indexOf(this.selectedYear);
            } else {
                this.yearFocusIndex = this.years.indexOf(this.bounds.defaultYear);
            }

            if (this.yearFocusIndex < 0) {
                this.yearFocusIndex = 0;
            }

            document.body.classList.add('jdp-open');

            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.scrollToYearIndex(this.yearFocusIndex, false);
                });
                this.$refs.panel?.focus();
            });
        },

        closePicker() {
            this.open = false;
            this.step = 'year';
            document.body.classList.remove('jdp-open');
        },

        onYearScroll(event) {
            this.yearScrollTop = event.target.scrollTop;
            const centerIndex = Math.round((this.yearScrollTop + YEAR_VIEWPORT_H / 2 - YEAR_ITEM_H / 2) / YEAR_ITEM_H);
            this.yearFocusIndex = Math.max(0, Math.min(this.years.length - 1, centerIndex));
        },

        scrollToYear(year, smooth = true) {
            const index = this.years.indexOf(year);
            if (index >= 0) {
                this.scrollToYearIndex(index, smooth);
            }
        },

        scrollToYearIndex(index, smooth = true) {
            const list = this.$refs.yearList;
            if (!list) {
                return;
            }

            const top = Math.max(0, index * YEAR_ITEM_H - (YEAR_VIEWPORT_H / 2) + (YEAR_ITEM_H / 2));
            list.scrollTo({ top, behavior: smooth ? 'smooth' : 'auto' });
            this.yearScrollTop = top;
            this.yearFocusIndex = index;
        },

        jumpToDecade(decade) {
            const target = this.years.find((y) => y >= decade && y < decade + 10) ?? decade;
            this.scrollToYear(target);
        },

        onYearInput() {
            const raw = toEnDigits(this.yearInput).replace(/\D/g, '').slice(0, 4);
            this.yearInput = this.usePersianDigits ? toFaDigits(raw) : raw;

            if (raw.length === 4) {
                const year = Number(raw);
                if (year >= this.bounds.minYear && year <= this.bounds.maxYear) {
                    this.selectYear(year);
                } else {
                    this.validationError = `سال باید بین ${this.digit(this.bounds.minYear)} و ${this.digit(this.bounds.maxYear)} باشد.`;
                }
            } else {
                this.validationError = '';
            }
        },

        selectYear(year) {
            this.pendingYear = year;
            this.yearFocusIndex = this.years.indexOf(year);
            this.validationError = '';
            this.step = 'month';
            this.monthFocusIndex = 0;

            this.$nextTick(() => this.$refs.monthGrid?.querySelector('[data-month-focus="0"]')?.focus());
        },

        backToYear() {
            this.step = 'year';
            this.pendingMonth = null;
            this.validationError = '';

            if (this.pendingYear) {
                this.yearFocusIndex = this.years.indexOf(this.pendingYear);
            } else if (this.selectedYear) {
                this.yearFocusIndex = this.years.indexOf(this.selectedYear);
            } else {
                this.yearFocusIndex = this.years.indexOf(this.bounds.defaultYear);
            }

            if (this.yearFocusIndex < 0) {
                this.yearFocusIndex = 0;
            }

            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.scrollToYearIndex(this.yearFocusIndex, false);
                });
            });
        },

        selectMonth(month) {
            this.pendingMonth = month;
            this.step = 'day';
            this.dayFocusIndex = 0;
            this.validationError = '';

            this.$nextTick(() => this.$refs.dayGrid?.querySelector('[data-day-focus="0"]')?.focus());
        },

        backToMonth() {
            this.step = 'month';
            this.validationError = '';
        },

        selectDay(day) {
            if (!isValidBirthDate(this.pendingYear, this.pendingMonth, day, this.minAge, this.maxAge)) {
                const age = getAgeInYears(this.pendingYear, this.pendingMonth, day);
                this.validationError = age < this.minAge
                    ? `حداقل سن ${this.digit(this.minAge)} سال است.`
                    : `حداکثر سن ${this.digit(this.maxAge)} سال است.`;

                return;
            }

            this.selectedYear = this.pendingYear;
            this.selectedMonth = this.pendingMonth;
            this.selectedDay = day;
            this.yearFocusIndex = this.years.indexOf(this.pendingYear);
            this.validationError = '';
            this.closePicker();
        },

        onPanelKeydown(event) {
            if (!this.open) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                this.closePicker();

                return;
            }

            if (this.step === 'year') {
                this.handleYearKeys(event);
            } else if (this.step === 'month') {
                this.handleMonthKeys(event);
            } else if (this.step === 'day') {
                this.handleDayKeys(event);
            }
        },

        handleYearKeys(event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.yearFocusIndex = Math.min(this.years.length - 1, this.yearFocusIndex + 1);
                this.scrollToYearIndex(this.yearFocusIndex);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.yearFocusIndex = Math.max(0, this.yearFocusIndex - 1);
                this.scrollToYearIndex(this.yearFocusIndex);
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.selectYear(this.years[this.yearFocusIndex]);
            } else if (event.key === 'ArrowRight' && this.decades.length) {
                event.preventDefault();
                const decade = this.decades.find((d) => d < (this.pendingYear ?? this.years[this.yearFocusIndex])) ?? this.decades[0];
                this.jumpToDecade(decade);
            } else if (event.key === 'ArrowLeft' && this.decades.length) {
                event.preventDefault();
                const decade = [...this.decades].reverse().find((d) => d > (this.pendingYear ?? this.years[this.yearFocusIndex])) ?? this.decades[this.decades.length - 1];
                this.jumpToDecade(decade);
            }
        },

        handleMonthKeys(event) {
            const cols = 3;
            const total = this.months.length;

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.monthFocusIndex = (this.monthFocusIndex + 1) % total;
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.monthFocusIndex = (this.monthFocusIndex - 1 + total) % total;
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.monthFocusIndex = Math.min(total - 1, this.monthFocusIndex + cols);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.monthFocusIndex = Math.max(0, this.monthFocusIndex - cols);
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.selectMonth(this.months[this.monthFocusIndex].index);
            } else if (event.key === 'Backspace') {
                event.preventDefault();
                this.backToYear();
            }
        },

        handleDayKeys(event) {
            const cols = 7;
            const total = this.days.length;
            if (!total) {
                return;
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.dayFocusIndex = Math.min(total - 1, this.dayFocusIndex + 1);
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.dayFocusIndex = Math.max(0, this.dayFocusIndex - 1);
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.dayFocusIndex = Math.min(total - 1, this.dayFocusIndex + cols);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.dayFocusIndex = Math.max(0, this.dayFocusIndex - cols);
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                const day = this.days[this.dayFocusIndex];
                if (day?.valid) {
                    this.selectDay(day.day);
                }
            } else if (event.key === 'Backspace') {
                event.preventDefault();
                this.backToMonth();
            }
        },

        isYearFocused(year) {
            return this.years[this.yearFocusIndex] === year;
        },
    };
}
