<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\SesiKegiatan;
use App\Models\SesiKegiatanDetail;
use App\Models\Kegiatan;
use App\Models\User;

class GroupNotifier
{
    public function buildTextForDate(string $dateWib): array
    {
        // Ambil sesi kegiatan berdasarkan tanggal
        $occ = SesiKegiatan::whereDate('session_date', $dateWib)->first();
        if (!$occ) {
            return [false, "Tidak ada sesi pada tanggal {$dateWib}."];
        }

        // Dapatkan hari untuk mencocokkan dengan kegiatan
        $weekday = strtolower(Carbon::parse($occ->session_date, 'Asia/Jakarta')->englishDayOfWeek);
        $map = [
            'monday' => 'mon',
            'wednesday' => 'wed',
            'thursday' => 'thu',
            'friday' => 'fri',
        ];

        $def = Kegiatan::where('weekday', $map[$weekday] ?? 'mon')->first();
        if (!$def) {
            return [false, "Tidak ditemukan definisi kegiatan untuk hari {$weekday}."];
        }

        // Ambil semua detail dengan user, filter sesuai jenis kegiatan
        $details = SesiKegiatanDetail::with('user')
            ->where('sesi_kegiatan_id', $occ->id)
            ->whereHas('user', function ($q) use ($def) {
                $q->where('is_admin', 0);

                if ($def->is_gabungan == 1) {
                    // Gabungan: hanya muda-mudi
                    //$q->where('is_muda_mudi', 1);
                } else {
                    // Dewasa: bukan muda-mudi
                    $q->where('is_muda_mudi', 0);
                }
            })
            ->get();

        // Hitung total kehadiran
        $countTepat = $details->where('status', 'hadir')->count();
        $countTelat = $details->where('status', 'terlambat')->count();
        $countHadir = $countTepat + $countTelat;
        $countIzin  = $details->where('status', 'izin')->count();
        $countTidak = $details->where('status', 'tidak_hadir')->count();

        // Format hari, tanggal, dan jam
        $hari = Carbon::parse($occ->session_date, 'Asia/Jakarta')->translatedFormat('l');
        $tgl  = Carbon::parse($occ->session_date, 'Asia/Jakarta')->translatedFormat('d M Y');
        $jam  = Carbon::parse($occ->start_at_local)->format('H:i') . ' - ' . Carbon::parse($occ->end_at_local)->format('H:i');

        // Mulai susun teks laporan
        $lines = [];
        $lines[] = "Rekap Kehadiran Sambung Kelompok {$hari}, {$tgl} ({$jam})";

        if ($def->is_gabungan == 1) {
            $lines[] = "(Kegiatan Sambung Kelompok)";
        } else {
            $lines[] = "(Kegiatan Sambung Kelompok)";
        }

        $lines[] = "";
        $lines[] = "📊 Rekapitulasi:";
        $lines[] = "• Hadir: {$countHadir}";
        $lines[] = "   └ Tepat waktu (19:45 - 20:00): {$countTepat}";
        $lines[] = "   └ Terlambat (di atas 20:00): {$countTelat}";
        $lines[] = "• Izin: {$countIzin}";
        $lines[] = "• Tidak hadir: {$countTidak}";

        // 🔸 IZIN
        if ($countIzin > 0) {
            $lines[] = "";
            $lines[] = "Izin:";
            if ($def->is_gabungan == 1) {
                // Muda-mudi
                $izinMuda = $details->where('status', 'izin')->where('user.jenis_kelamin', 1)
                    ->pluck('user.name')->filter()->values()->all();
                $izinMudi = $details->where('status', 'izin')->where('user.jenis_kelamin', 2)
                    ->pluck('user.name')->filter()->values()->all();

                if ($izinMuda) {
                    $lines[] = "   • Muda (L):";
                    foreach ($izinMuda as $n) $lines[] = "     - {$n}";
                }
                if ($izinMudi) {
                    $lines[] = "   • Mudi (P):";
                    foreach ($izinMudi as $n) $lines[] = "     - {$n}";
                }
            } else {
                // Dewasa
                $izinLaki = $details->where('status', 'izin')->where('user.jenis_kelamin', 1)
                    ->map(function ($d) {
                        return $d->user->is_usia_nikah == 1
                            ? "Mas {$d->user->name}"
                            : "Bpk. {$d->user->name}";
                    })->values()->all();

                $izinPerem = $details->where('status', 'izin')->where('user.jenis_kelamin', 2)
                    ->map(function ($d) {
                        return $d->user->is_usia_nikah == 1
                            ? "Mbak {$d->user->name}"
                            : "Ibu {$d->user->name}";
                    })->values()->all();

                if ($izinLaki) {
                    $lines[] = "   • Laki-laki:";
                    foreach ($izinLaki as $n) $lines[] = "     - {$n}";
                }
                if ($izinPerem) {
                    $lines[] = "   • Perempuan:";
                    foreach ($izinPerem as $n) $lines[] = "     - {$n}";
                }
            }
        }

        // 🔸 TIDAK HADIR
        if ($countTidak > 0) {
            $lines[] = "";
            $lines[] = "Tidak hadir:";
            if ($def->is_gabungan == 1) {
                // Muda-mudi
                $tidakMuda = $details->where('status', 'tidak_hadir')->where('user.jenis_kelamin', 1)
                    ->pluck('user.name')->filter()->values()->all();
                $tidakMudi = $details->where('status', 'tidak_hadir')->where('user.jenis_kelamin', 2)
                    ->pluck('user.name')->filter()->values()->all();

                if ($tidakMuda) {
                    $lines[] = "   • Muda (L):";
                    foreach ($tidakMuda as $n) $lines[] = "     - {$n}";
                }
                if ($tidakMudi) {
                    $lines[] = "   • Mudi (P):";
                    foreach ($tidakMudi as $n) $lines[] = "     - {$n}";
                }
            } else {
                // Dewasa
                $tidakLaki = $details->where('status', 'tidak_hadir')->where('user.jenis_kelamin', 1)
                    ->map(function ($d) {
                        return $d->user->is_usia_nikah == 1
                            ? "Mas {$d->user->name}"
                            : "Bpk. {$d->user->name}";
                    })->values()->all();

                $tidakPerem = $details->where('status', 'tidak_hadir')->where('user.jenis_kelamin', 2)
                    ->map(function ($d) {
                        return $d->user->is_usia_nikah == 1
                            ? "Mbak {$d->user->name}"
                            : "Ibu {$d->user->name}";
                    })->values()->all();

                if ($tidakLaki) {
                    $lines[] = "   • Laki-laki:";
                    foreach ($tidakLaki as $n) $lines[] = "     - {$n}";
                }
                if ($tidakPerem) {
                    $lines[] = "   • Perempuan:";
                    foreach ($tidakPerem as $n) $lines[] = "     - {$n}";
                }
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
