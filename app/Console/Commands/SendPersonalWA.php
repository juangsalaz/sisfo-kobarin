<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WaPersonalService;
use App\Models\SesiKegiatan;
use App\Models\SesiKegiatanDetail;
use Carbon\Carbon;

class SendPersonalWA extends Command
{
    protected $signature = 'wa:send-personal {date?}';
    protected $description = 'Kirim WA personal ke user tidak hadir pada tanggal tertentu';

    public function handle(WaPersonalService $wa)
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'), 'Asia/Jakarta')->toDateString()
            : now('Asia/Jakarta')->toDateString();

        // Ambil semua sesi pada tanggal tsb
        $sesi = SesiKegiatan::whereDate('session_date', $date)->pluck('id');
        if ($sesi->isEmpty()) {
            $this->warn("Tidak ada sesi pada {$date}");
            return Command::SUCCESS;
        }

        // Ambil user tidak hadir
        $absents = SesiKegiatanDetail::with('user')
            ->whereIn('sesi_kegiatan_id', $sesi)
            ->where('status', 'tidak_hadir')
            ->get()
            ->pluck('user.no_hp')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($absents)) {
            $this->info("Tidak ada user tidak hadir di {$date}");
            return Command::SUCCESS;
        }

        $text = "Assalamualaikum, Ngapunten, dalu niki kok mboten ketingal ngaji sambung, sakit nopo wonten urusan? 🙏🏼";

        $res = $wa->sendPersonal($absents, $text);

        if ($res['ok']) {
            $this->info("Pesan WA personal terkirim ke ".count($absents)." nomor.");
        } else {
            $this->error("Gagal kirim WA personal: ".json_encode($res['body']));
        }

        return Command::SUCCESS;
    }
}
