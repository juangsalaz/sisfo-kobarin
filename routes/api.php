<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Attendance\FingerspotWebhookController;
use App\Http\Controllers\Attendance\FonnteWebhookController;

Route::post('/webhooks/fingerspot', [FingerspotWebhookController::class, 'handle']);
Route::post('/webhooks/fonnte', [FonnteWebhookController::class, 'handle']);

