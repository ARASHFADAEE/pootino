<?php

if (! function_exists('fa_num')) {
    function fa_num(int $n): string
    {
        return str_replace(range(0, 9), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $n);
    }
}

if (! function_exists('fa_time_ago')) {
    function fa_time_ago(\Carbon\CarbonInterface $date): string
    {
        $diffDays = (int) $date->diffInDays(now());

        return match (true) {
            $diffDays === 0 => 'امروز',
            $diffDays === 1 => 'دیروز',
            $diffDays === 2 => '۲ روز پیش',
            $diffDays <= 6 => fa_num($diffDays).' روز پیش',
            $diffDays <= 29 => fa_num((int) ceil($diffDays / 7)).' هفته پیش',
            $diffDays <= 364 => fa_num((int) ceil($diffDays / 30)).' ماه پیش',
            default => fa_num((int) ceil($diffDays / 365)).' سال پیش',
        };
    }
}

if (! function_exists('jalali_age_years')) {
    function jalali_age_years(int $jy, int $jm, int $jd): int
    {
        $today = \Morilog\Jalali\Jalalian::now();
        $age = $today->getYear() - $jy;
        if ($today->getMonth() < $jm || ($today->getMonth() === $jm && $today->getDay() < $jd)) {
            $age -= 1;
        }

        return $age;
    }
}

if (! function_exists('is_valid_jalali_birth_age')) {
    function is_valid_jalali_birth_age(string $birthDate, int $minAge = 18, int $maxAge = 120): bool
    {
        if (! preg_match('/^(\d{4})\/(\d{2})\/(\d{2})$/', $birthDate, $m)) {
            return false;
        }

        $age = jalali_age_years((int) $m[1], (int) $m[2], (int) $m[3]);

        return $age >= $minAge && $age <= $maxAge;
    }
}
