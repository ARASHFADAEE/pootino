<?php

namespace App\Providers;

use App\Services\SmsIrService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsIrService::class, fn () => new SmsIrService());
    }

    public function boot(): void
    {
        if (! function_exists('fa_num')) {
            function fa_num(int $n): string
            {
                return str_replace(range(0, 9), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $n);
            }
        }
    }
}
