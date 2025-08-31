<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:aggregate')
    ->mondays()
    ->dailyAt('21:20')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:aggregate')
    ->thursdays()
    ->dailyAt('21:20')
    ->timezone('Asia/Jakarta');

