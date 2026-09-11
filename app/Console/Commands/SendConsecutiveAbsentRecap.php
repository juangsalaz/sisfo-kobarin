<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConsecutiveAbsentNotifier;
use Carbon\Carbon;

class SendConsecutiveAbsentRecap extends Command
{
    protected $signature = 'attendance:send-3x-absent-recap {date?} {--target=}';
    protected $description = 'Kirim laporan jamaah yang 3 kali pengajian berturut-turut tidak hadir ke WhatsApp Group via Fonnte';

    public function handle(ConsecutiveAbsentNotifier $notifier)
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'), 'Asia/Jakarta')->toDateString()
            : now('Asia/Jakarta')->toDateString();

        $target = $this->option('target') ?: null;

        $this->info("Memproses pemeriksaan 3x tidak hadir berturut-turut untuk sesi per tanggal {$date}...");

        $result = $notifier->recapAndSend($date, $target);

        if ($result['ok']) {
            $msg = is_string($result['body']) ? $result['body'] : "Laporan 3x tidak hadir berturut-turut berhasil diproses. (HTTP {$result['status']})";
            $this->info($msg);
            return Command::SUCCESS;
        }

        $this->error("Gagal mengirim laporan: " . (is_string($result['body']) ? $result['body'] : json_encode($result['body'])));
        return Command::FAILURE;
    }
}
