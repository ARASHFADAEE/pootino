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

    public function boot(): void {}
}
