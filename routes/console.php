<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('quote')
    ->everyMinute()
    ->description('Display an inspiring quote')
    ->sendOutputTo('quote.txt', true);

Schedule::command('reports:generate')
    ->everyMinute()
    ->sendOutputTo('reports.txt', true)
    ->description('Generate daily product report');

Schedule::command('emails:daily')
    ->everyMinute()
    ->sendOutputTo(storage_path('logs/emails.txt'))
    ->description('Send daily summary email');
