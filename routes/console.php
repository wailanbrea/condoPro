<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bills:generate')->monthlyOn(1, '02:00');
Schedule::command('bills:mark-overdue')->dailyAt('00:30');
Schedule::command('db:backup')->dailyAt('03:00');
