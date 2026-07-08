<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/**
 * Vercel has no persistent cron daemon. Vercel Cron Jobs (configured in vercel.json) hit
 * this route once a day to run Bagisto's daily maintenance commands directly, since a
 * Hobby-plan cron can't fire per-minute to drive Laravel's schedule:run at each task's
 * exact configured time. Protected by the CRON_SECRET env var, which Vercel automatically
 * sends as a Bearer token on cron requests.
 */
Route::get('/cron/schedule', function () {
    $secret = env('CRON_SECRET');

    abort_unless(
        $secret && request()->bearerToken() === $secret,
        401
    );

    Artisan::call('indexer:index', ['--type' => ['price']]);
    Artisan::call('product:price-rule:index');
    Artisan::call('campaign:process');
    Artisan::call('invoice:cron');

    if (core()->getConfigData('general.exchange_rates.schedule.enabled')) {
        Artisan::call('exchange-rate:update');
    }

    return response('Daily maintenance commands executed.');
});
