<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FingerprintReminderNotifier;

class SendFingerprintReminder extends Command
{
    protected $signature = 'attendance:send-fingerprint-reminder {--target=}';
    protected $description = 'Kirim pesan pengingat input izin/absen manual ke WhatsApp Group via Fonnte (21:15 WIB)';

    public function handle(FingerprintReminderNotifier $notifier)
    {
        $target = $this->option('target') ?: null;

        $this->info("Mengirim pesan pengingat presensi ke WhatsApp Group...");

        $result = $notifier->sendToGroup($target);

        if ($result['ok']) {
            $this->info("Pesan pengingat presensi berhasil terkirim. (HTTP {$result['status']})");
            return Command::SUCCESS;
        }

        $this->error("Gagal mengirim pesan pengingat: " . (is_string($result['body']) ? $result['body'] : json_encode($result['body'])));
        return Command::FAILURE;
    }
}
