export const persianMonths = [
    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
];

const FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';

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

export function j2d(jy, jm, jd) {
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

export function jalaaliMonthLength(jy, jm) {
    if (jm <= 6) {
        return 31;
    }
    if (jm <= 11) {
        return 30;
    }

    return g2d(jalCal(jy).gy + 1, 3, jalCal(jy).march) - g2d(jalCal(jy).gy, 3, jalCal(jy).march) - 336;
}

export function parseJalali(value) {
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

export function pad(value) {
    return String(value).padStart(2, '0');
}

export function formatJalali(year, month, day) {
    return `${year}/${pad(month)}/${pad(day)}`;
}

export function todayJalali() {
    const now = new Date();

    return d2j(g2d(now.getFullYear(), now.getMonth() + 1, now.getDate()));
}

export function toFaDigits(value) {
    return String(value).replace(/\d/g, (d) => FA_DIGITS[Number(d)]);
}

export function toEnDigits(value) {
    return String(value).replace(/[۰-۹]/g, (d) => String(FA_DIGITS.indexOf(d)));
}

export function getAgeInYears(jy, jm, jd) {
    const today = todayJalali();
    let age = today.jy - jy;
    if (today.jm < jm || (today.jm === jm && today.jd < jd)) {
        age -= 1;
    }

    return age;
}

export function isValidBirthDate(jy, jm, jd, minAge = 18, maxAge = 120) {
    if (jm < 1 || jm > 12 || jd < 1 || jd > jalaaliMonthLength(jy, jm)) {
        return false;
    }

    const age = getAgeInYears(jy, jm, jd);

    return age >= minAge && age <= maxAge;
}

export function getBirthYearBounds(minAge = 18, maxAge = 120) {
    const today = todayJalali();

    return {
        minYear: today.jy - maxAge,
        maxYear: today.jy - minAge,
        defaultYear: today.jy - 30,
    };
}

export function getDecades(minYear, maxYear) {
    const decades = new Set();
    for (let y = minYear; y <= maxYear; y += 1) {
        decades.add(Math.floor(y / 10) * 10);
    }

    return [...decades].sort((a, b) => b - a);
}
