<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly digest — every Monday 9am each tenant admin gets a summary email
Schedule::command('tenant:digest')->weeklyOn(1, '09:00');
