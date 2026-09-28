<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:aggregate')
    ->mondays()
    ->dailyAt('23:00')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:aggregate')
    ->thursdays()
    ->dailyAt('23:00')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:aggregate')
    ->fridays()
    ->dailyAt('23:00')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-recap')
    ->mondays()
    ->dailyAt('23:15')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-recap')
    ->thursdays()
    ->dailyAt('23:15')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-recap')
    ->fridays()
    ->dailyAt('23:15')
    ->timezone('Asia/Jakarta');

// Schedule::command('wa:send-personal')
//     ->mondays()
//     ->dailyAt('23:16')
//     ->timezone('Asia/Jakarta');

// Schedule::command('wa:send-personal')
//     ->thursdays()
//     ->dailyAt('23:16')
//     ->timezone('Asia/Jakarta');

// Schedule::command('wa:send-personal')
//     ->fridays()
//     ->dailyAt('23:16')
//     ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-monthly-absent-recap')
    ->monthlyOn(1, '08:00')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-fingerprint-reminder')
    ->mondays()
    ->dailyAt('21:15')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-fingerprint-reminder')
    ->thursdays()
    ->dailyAt('21:15')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-fingerprint-reminder')
    ->fridays()
    ->dailyAt('21:15')
    ->timezone('Asia/Jakarta');


