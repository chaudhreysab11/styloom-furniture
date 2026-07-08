<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/**
 * Vercel has no persistent cron daemon, so Vercel Cron Jobs (configured in vercel.json)
 * hit this route once a minute to drive Laravel's scheduler instead. Protected by the
 * CRON_SECRET env var, which Vercel automatically sends as a Bearer token on cron requests.
 */
Route::get('/cron/schedule', function () {
    $secret = env('CRON_SECRET');

    abort_unless(
        $secret && request()->bearerToken() === $secret,
        401
    );

    Artisan::call('schedule:run');

    return response('Scheduler executed.');
});
