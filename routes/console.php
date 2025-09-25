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

Schedule::command('attendance:send-recap')
    ->mondays()
    ->dailyAt('21:45')
    ->timezone('Asia/Jakarta');

Schedule::command('attendance:send-recap')
    ->thursdays()
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


