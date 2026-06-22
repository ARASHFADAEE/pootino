const persianMonths = [
    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
];

const persianWeekdays = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];

function mod(a, b) {
    return ((a % b) + b) % b;
}

function div(a, b) {
    return ~~(a / b);
}

function jalCal(jy) {
    const breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
    const bl = breaks.length;
    const gy = jy + 621;
    let leapJ = -14;
    let jp = breaks[0];
    let jm;
    let jump;
    let leap;
    let n;
    let i;

    if (jy < jp || jy >= breaks[bl - 1]) {
        throw new Error('Invalid Jalali year');
    }

    for (i = 1; i < bl; i += 1) {
        jm = breaks[i];
        jump = jm - jp;
        if (jy < jm) {
            break;
        }
        leapJ += div(jump, 33) * 8 + div((jump % 33), 4);
        jp = jm;
    }

    n = jy - jp;
    leapJ += div(n, 33) * 8 + div((n % 33) + 3, 4);
    if ((jump % 33) === 4 && jump - n === 4) {
        leapJ += 1;
    }

    leap = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;
    const march = 20 + leapJ - leap;

    return { gy, march };
}

function g2d(gy, gm, gd) {
    let d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4)
        + div(153 * mod(gm + 9, 12) + 2, 5)
        + gd - 34840408;
    d -= div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;

    return d;
}

function d2g(jdn) {
    let j = 4 * jdn + 139361631;
    j += div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
    const i = div(mod(j, 1461), 4) * 5 + 308;
    const gd = div(mod(i, 153), 5) + 1;
    const gm = mod(div(i, 153), 12) + 1;
    const gy = div(j, 1461) - 100100 + div(8 - gm, 6);

    return { gy, gm, gd };
}

function j2d(jy, jm, jd) {
    const r = jalCal(jy);

    return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1;
}

function d2j(jdn) {
    const g = d2g(jdn);
    let jy = g.gy - 621;
    const r = jalCal(jy);
    let jdn1f = g2d(r.gy, 3, r.march);
    let k = jdn - jdn1f;
    let jm;
    let jd;

    if (k >= 0) {
        if (k <= 185) {
            jm = 1 + div(k, 31);
            jd = mod(k, 31) + 1;

            return { jy, jm, jd };
        }

        k -= 186;
    } else {
        jy -= 1;
        k += 179;
        if (jalCal(jy).march === 31) {
            k += 1;
        }
    }

    jm = 7 + div(k, 30);
    jd = mod(k, 30) + 1;

    return { jy, jm, jd };
}

function jalaaliMonthLength(jy, jm) {
    if (jm <= 6) {
        return 31;
    }
    if (jm <= 11) {
        return 30;
    }

    return g2d(jalCal(jy).gy + 1, 3, jalCal(jy).march) - g2d(jalCal(jy).gy, 3, jalCal(jy).march) - 336;
}

function parseJalali(value) {
    const match = String(value || '').match(/^(\d{4})\/(\d{2})\/(\d{2})$/);
    if (!match) {
        return null;
    }

    return {
        year: Number(match[1]),
        month: Number(match[2]),
        day: Number(match[3]),
    };
}

function pad(value) {
    return String(value).padStart(2, '0');
}

function formatJalali(year, month, day) {
    return `${year}/${pad(month)}/${pad(day)}`;
}

function todayJalali() {
    const now = new Date();

    return d2j(g2d(now.getFullYear(), now.getMonth() + 1, now.getDate()));
}

function weekdayOf(jy, jm, jd) {
    return mod(j2d(jy, jm, jd) + 1, 7);
}

export function jalaliDatePicker(initialValue = '') {
    const parsed = parseJalali(initialValue);
    const today = todayJalali();

    return {
        open: false,
        year: parsed?.year ?? today.jy - 20,
        month: parsed?.month ?? 1,
        day: parsed?.day ?? 1,
        selectedYear: parsed?.year ?? null,
        selectedMonth: parsed?.month ?? null,
        selectedDay: parsed?.day ?? null,

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

            return `${this.selectedDay} ${persianMonths[this.selectedMonth - 1]} ${this.selectedYear}`;
        },

        get monthLabel() {
            return `${persianMonths[this.month - 1]} ${this.year}`;
        },

        get weekdays() {
            return persianWeekdays;
        },

        get days() {
            const length = jalaaliMonthLength(this.year, this.month);
            const firstWeekday = weekdayOf(this.year, this.month, 1);
            const cells = [];

            for (let i = 0; i < firstWeekday; i += 1) {
                cells.push({ empty: true, key: `e-${i}` });
            }

            for (let d = 1; d <= length; d += 1) {
                cells.push({
                    empty: false,
                    day: d,
                    key: `d-${d}`,
                    selected: this.selectedYear === this.year
                        && this.selectedMonth === this.month
                        && this.selectedDay === d,
                });
            }

            return cells;
        },

        prevMonth() {
            if (this.month === 1) {
                this.month = 12;
                this.year -= 1;
                return;
            }
            this.month -= 1;
        },

        nextMonth() {
            if (this.month === 12) {
                this.month = 1;
                this.year += 1;
                return;
            }
            this.month += 1;
        },

        selectDay(day) {
            this.selectedYear = this.year;
            this.selectedMonth = this.month;
            this.selectedDay = day;
            this.open = false;
        },

        toggle() {
            this.open = !this.open;
        },
    };
}
