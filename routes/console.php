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

Schedule::command('attendance:aggregate')
    ->fridays()
    ->dailyAt('21:20')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-recap')
    ->mondays()
    ->dailyAt('21:45')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-recap')
    ->thursdays()
    ->dailyAt('21:45')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-recap')
    ->fridays()
    ->dailyAt('21:45')
    ->timezone('Asia/Jakarta');

Schedule::command('wa:send-personal')
    ->mondays()
    ->dailyAt('21:46')
    ->timezone('Asia/Jakarta');

Schedule::command('wa:send-personal')
    ->thursdays()
    ->dailyAt('21:46')
    ->timezone('Asia/Jakarta');

Schedule::command('wa:send-personal')
    ->fridays()
    ->dailyAt('21:46')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-monthly-absent-recap')
    ->dailyAt('21:00')
    ->timezone('Asia/Jakarta')
    ->when(function () {
        $today = now('Asia/Jakarta');
        return ($today->month === 2) ? ($today->day === 28) : ($today->day === 30);
    });


