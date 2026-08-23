<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('system:health-check --source=scheduled')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

Schedule::command('messaging:dispatch-triggers')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->onOneServer();
