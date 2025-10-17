<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KehadiranController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Attendance\FingerspotWebhookController;
use App\Services\GroupNotifier;
use Illuminate\Http\Request;

Route::get('/', fn() => view('welcome'));

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('admin/users', UserController::class)->names('admin.users');
    
    Route::get('admin/kehadiran', [KehadiranController::class, 'index'])
        ->name('kehadiran.index');
    Route::get('admin/kehadiran/create', [KehadiranController::class, 'create'])
        ->name('kehadiran.create');
    Route::post('admin/kehadiran', [KehadiranController::class, 'store'])->name('kehadiran.store');
    Route::delete('/kehadiran/{id}', [KehadiranController::class, 'destroy'])
        ->name('kehadiran.destroy');

}); 


Route::post('/admin/attendance/send-recap', function(Request $req, GroupNotifier $notifier) {
    $date = $req->input('date', now('Asia/Jakarta')->toDateString());
    $group= $req->input('group');
    $res  = $notifier->recapAndSend($date, $group);
    return back()->with('status', $res['ok'] ? 'Rekap terkirim' : 'Gagal kirim: '.json_encode($res['body']));
})->middleware('auth');

require __DIR__.'/auth.php';
