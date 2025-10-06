<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\SesiKegiatan;
use App\Models\SesiKegiatanDetail;
use App\Models\User;

class GroupNotifier
{
    public function buildTextForDate(string $dateWib): array
    {
        // dateWib format Y-m-d (WIB)
        $occ = SesiKegiatan::whereDate('session_date', $dateWib)->first();
        if (!$occ) {
            return [false, "Tidak ada sesi pada tanggal {$dateWib}."];
        }

        // ambil semua detail + user
        $details = SesiKegiatanDetail::with('user')
            ->where('sesi_kegiatan_id', $occ->id)
            ->whereHas('user', function ($q) {
                $q->where('is_admin', 0);
            })
            ->get();

        $countTepat = $details->where('status', 'hadir')->count();
        $countTelat = $details->where('status', 'terlambat')->count();
        $countHadir = $countTepat + $countTelat;
        $countTidak = $details->where('status', 'tidak_hadir')->count();
        $countIzin = $details->where('status', 'izin')->count();

        $absentNamesLaki = $details->where('status', 'tidak_hadir')->where('user.jenis_kelamin', 1)
            ->pluck('user.name')
            ->filter()
            ->values()
            ->all();

        $absentNamesPerempuan = $details->where('status', 'tidak_hadir')->where('user.jenis_kelamin', 2)
            ->pluck('user.name')
            ->filter()
            ->values()
            ->all();

        $izinNamesLaki = $details->where('status', 'izin')->where('user.jenis_kelamin', 1)
            ->pluck('user.name')
            ->filter()
            ->values()
            ->all();

        $izinNamesPerempuan = $details->where('status', 'izin')->where('user.jenis_kelamin', 2)
            ->pluck('user.name')
            ->filter()
            ->values()
            ->all();

        $hari  = Carbon::parse($occ->session_date, 'Asia/Jakarta')->translatedFormat('l'); // Senin/Kamis
        $tgl   = Carbon::parse($occ->session_date, 'Asia/Jakarta')->translatedFormat('d M Y');
        $jam   = Carbon::parse($occ->start_at_local)->format('H:i') . ' - ' . Carbon::parse($occ->end_at_local)->format('H:i');

        $lines = [];
        $lines[] = "Rekap Kehadiran Sambung Kelompok {$hari}, {$tgl} ({$jam})";
        $lines[] = "• Hadir: {$countHadir}";
        $lines[] = "   └ Tepat waktu (19:45 - 20:00): {$countTepat}";
        $lines[] = "   └ Terlambat (di atas 20:00): {$countTelat}";
        $lines[] = "• Izin: {$countIzin}";
        $lines[] = "• Tidak hadir: {$countTidak}";
        
        if ($countIzin > 0) {
            $lines[] = "";
            $lines[] = "Izin (Laki-laki):";
            foreach ($izinNamesLaki as $n) {
                $lines[] = "- Bpk. {$n}";
            }
        }

        if ($countIzin > 0) {
            $lines[] = "";
            $lines[] = "Izin (Perempuan):";
            foreach ($izinNamesPerempuan as $n) {
                $lines[] = "- Ibu {$n}";
            }
        }

        if ($countTidak > 0) {
            $lines[] = "";
            $lines[] = "Tidak hadir (Laki-laki):";
            foreach ($absentNamesLaki as $n) {
                $lines[] = "- Bpk. {$n}";
            }
        }

        if ($countTidak > 0) {
            $lines[] = "";
            $lines[] = "Tidak hadir (Perempuan):";
            foreach ($absentNamesPerempuan as $n) {
                $lines[] = "- Ibu {$n}";
            }
        }

        $text = implode("\n", $lines);
        return [true, $text];
    }

    /**
     * Kirim teks ke group via API.
     */
    public function sendToGroup(string $text, ?string $groupName = null): array
    {
        $base = rtrim(config('services.group_api.base_url'), '/');
        $key  = (string) config('services.group_api.api_key');
        $grp  = $groupName ?: (string) config('services.group_api.group');

        try {
            $res = Http::withHeaders(['x-api-key' => $key])
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->retry(2, 500)
                ->post($base . '/send-group', [
                    'groupName' => $grp,
                    'text'      => $text,
                ]);

            return [
                'ok'     => $res->successful(),
                'status' => $res->status(),
                'body'   => rescue(fn() => $res->json(), $res->body(), report:false),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => ['error' => $e->getMessage()]];
        }
    }

    /**
     * Satu pintu: build rekap + kirim
     */
    public function recapAndSend(string $dateWib, ?string $groupName = null): array
    {
        [$ok, $text] = $this->buildTextForDate($dateWib);
        if (!$ok) return ['ok' => false, 'status' => 0, 'body' => $text];

        return $this->sendToGroup($text, $groupName);
    }
}
