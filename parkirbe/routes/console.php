<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Schedule
|--------------------------------------------------------------------------
|
| Jadwal otomatis untuk command parkir.
| Pastikan cron job Laravel sudah terpasang di server:
|   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
|
*/

/** Generate tagihan bulanan setiap tanggal 1 jam 00:05 */
Schedule::command('parking:generate-bills')
    ->monthlyOn(1, '00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('parking:generate-bills GAGAL berjalan.');
    });
