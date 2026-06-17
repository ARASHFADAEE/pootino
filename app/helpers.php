<?php

if (! function_exists('fa_num')) {
    function fa_num(int $n): string
    {
        return str_replace(range(0, 9), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $n);
    }
}
