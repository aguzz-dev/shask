<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Ciclo de vida de buzones. Requiere el cron del hosting:
//   * * * * * php artisan schedule:run
Schedule::command('posts:notify-lifecycle')->everyFifteenMinutes();
Schedule::command('streak:tick')->dailyAt('23:50');
Schedule::command('streak:warn')->dailyAt('19:00');

// Push al creador por adquisición de su diseño (creator-acquisition-push).
Schedule::command('push:flush-quiet-hours')->everyFifteenMinutes();
Schedule::command('push:creator-digest')->dailyAt('20:30');
