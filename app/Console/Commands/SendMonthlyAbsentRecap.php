<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlyAbsentNotifier;

class SendMonthlyAbsentRecap extends Command
{
    protected $signature = 'attendance:send-monthly-absent-recap {--target=} {--days=30}';
    protected $description = 'Kirim rekapitulasi 10 jamaah Laki-laki & Perempuan paling jarang hadir (1 bulan) ke WhatsApp via Fonnte';

    public function handle(MonthlyAbsentNotifier $notifier)
    {
        $target = $this->option('target') ?: null;
        $days   = (int) ($this->option('days') ?: 30);

        $this->info("Menyusun dan mengirim rekap 10 jamaah paling jarang hadir ({$days} hari terakhir)...");

        $result = $notifier->recapAndSend($target, $days);

        if ($result['ok']) {
            $this->info("Rekapitulasi berhasil terkirim ke WhatsApp Group via Fonnte. (HTTP {$result['status']})");
            return Command::SUCCESS;
        }

        $this->error("Gagal mengirim rekapitulasi: " . (is_string($result['body']) ? $result['body'] : json_encode($result['body'])));
        return Command::FAILURE;
    }
}
