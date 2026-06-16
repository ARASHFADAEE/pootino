<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('Inspirational quote disabled.');
});

Schedule::command('ads:expire')->dailyAt('00:00');
