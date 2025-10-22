<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FingerspotService;
use App\Models\User;

class SyncAllUsers extends Command
{
    protected $signature = 'users:sync-all';
    protected $description = 'Kirim semua user ke mesin Fingerspot';

    public function handle(FingerspotService $svc)
    {
        $users = User::where('is_admin', 0)->where('jenis_kelamin', 2)->where('is_usia_nikah', 1)->get();

        foreach ($users as $user) {
            $res = $svc->setUserInfo($user);
            $user->forceFill([
                'synced_at'        => now(),
                'last_sync_status' => $res['ok'] ? 'success' : ('failed: '.$res['status']),
            ])->save();

            $this->info("User {$user->name}: {$user->last_sync_status}");
        }

        return Command::SUCCESS;
    }
}
