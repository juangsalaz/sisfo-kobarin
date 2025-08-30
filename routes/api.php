<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Attendance\FingerspotWebhookController;

Route::post('/webhooks/fingerspot', [FingerspotWebhookController::class, 'handle']);
