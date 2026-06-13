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
