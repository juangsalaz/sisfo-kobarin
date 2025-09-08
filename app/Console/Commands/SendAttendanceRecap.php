<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GroupNotifier;
use Carbon\Carbon;

class SendAttendanceRecap extends Command
{
    protected $signature = 'attendance:send-recap {date?} {--group=}';
    protected $description = 'Kirim rekap kehadiran ke grup WhatsApp';

    public function handle(GroupNotifier $notifier)
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'), 'Asia/Jakarta')->toDateString()
            : now('Asia/Jakarta')->toDateString();

        $group = $this->option('group') ?: null;

        $result = $notifier->recapAndSend($date, $group);

        if ($result['ok']) {
            $this->info("Rekap untuk {$date} terkirim. (HTTP {$result['status']})");
            return Command::SUCCESS;
        }

        $this->error("Gagal mengirim rekap: " . (is_string($result['body']) ? $result['body'] : json_encode($result['body'])));
        return Command::FAILURE;
    }
}
