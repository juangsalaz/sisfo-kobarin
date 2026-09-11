<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyAbsentNotifier
{
    /**
     * Ambil 10 jamaah paling jarang hadir berdasarkan gender (Non Muda-Mudi).
     */
    public function getTopAbsenteesByGender(int $gender, int $days = 30)
    {
        $startDate = Carbon::now('Asia/Jakarta')->subDays($days)->toDateString();

        return DB::table('users as u')
            ->select(
                'u.id',
                'u.name',
                'u.jenis_kelamin',
                'u.is_muda_mudi',
                'u.is_usia_nikah',
                DB::raw("SUM(CASE WHEN skd.status IN ('hadir', 'terlambat') THEN 1 ELSE 0 END) as jumlah_hadir"),
                DB::raw("SUM(CASE WHEN skd.status = 'tidak_hadir' THEN 1 ELSE 0 END) as jumlah_tidak_hadir"),
                DB::raw("SUM(CASE WHEN skd.status = 'izin' THEN 1 ELSE 0 END) as jumlah_izin")
            )
            ->join('sesi_kegiatan_detail as skd', 'skd.user_id', '=', 'u.id')
            ->join('sesi_kegiatan as sk', 'sk.id', '=', 'skd.sesi_kegiatan_id')
            ->where('u.is_admin', 0)
            ->where('u.is_muda_mudi', 0)
            ->where('u.jenis_kelamin', $gender)
            ->where('sk.session_date', '>=', $startDate)
            ->groupBy('u.id', 'u.name', 'u.jenis_kelamin', 'u.is_muda_mudi', 'u.is_usia_nikah')
            ->orderByDesc('jumlah_tidak_hadir')
            ->orderBy('jumlah_hadir', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Menyusun format pesan WhatsApp rekapitulasi 10 jamaah paling jarang hadir.
     */
    public function buildText(int $days = 30): string
    {
        $laki = $this->getTopAbsenteesByGender(1, $days);
        $perempuan = $this->getTopAbsenteesByGender(2, $days);

        $nowFormatted = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y');

        $lines = [];
        $lines[] = "🤖 *Pesan ini dikirim otomatis dari sistem*";
        $lines[] = "";
        $lines[] = "📋 *Rekapitulasi 10 Jamaah Paling Jarang Hadir ({$days} Hari Terakhir)*";
        $lines[] = "📅 Per Tanggal: {$nowFormatted}";
        $lines[] = "📌 *(Kategori Bapak-bapak, Ibu-ibu & Usia Nikah)*";
        $lines[] = "";

        // Section Laki-laki
        $lines[] = "👨 *JAMAAH LAKI-LAKI:*";
        if ($laki->isEmpty()) {
            $lines[] = "• Tidak ada data / Semua hadir.";
        } else {
            foreach ($laki as $idx => $user) {
                $no = $idx + 1;
                $panggilan = $user->is_usia_nikah == 1 ? "Mas" : "Bpk.";
                $lines[] = "{$no}. {$panggilan} {$user->name} - *{$user->jumlah_tidak_hadir} Sesi Absen* (Hadir: {$user->jumlah_hadir} | Izin: {$user->jumlah_izin})";
            }
        }

        $lines[] = "";

        // Section Perempuan
        $lines[] = "🧕🏼 *JAMAAH PEREMPUAN:*";
        if ($perempuan->isEmpty()) {
            $lines[] = "• Tidak ada data / Semua hadir.";
        } else {
            foreach ($perempuan as $idx => $user) {
                $no = $idx + 1;
                $panggilan = $user->is_usia_nikah == 1 ? "Mbak" : "Ibu";
                $lines[] = "{$no}. {$panggilan} {$user->name} - *{$user->jumlah_tidak_hadir} Sesi Absen* (Hadir: {$user->jumlah_hadir} | Izin: {$user->jumlah_izin})";
            }
        }

        $lines[] = "";
        $lines[] = "💬 *Catatan:* Laporan ini disusun otomatis untuk perhatian pengurus dalam meningkatkan keaktifan sambung kelompok. Semoga Allah memberikan kelancaran dan kebarokahan untuk kita semua. 🤲";

        return implode("\n", $lines);
    }

    /**
     * Kirim teks rekapitulasi ke WhatsApp Group via Fonnte API.
     */
    public function sendToGroup(string $text, ?string $target = null): array
    {
        $token  = (string) config('services.fonnte.token');
        $target = $target ?: (string) config('services.fonnte.absent_group_target');

        try {
            $res = Http::withHeaders([
                    'Authorization' => $token,
                ])
                ->asForm()
                ->timeout(15)
                ->retry(2, 500)
                ->post('https://api.fonnte.com/send', [
                    'target'      => $target,
                    'message'     => $text,
                    'countryCode' => '62',
                ]);

            $body = rescue(fn() => $res->json(), $res->body(), report: false);
            $statusFonnte = is_array($body) ? ($body['status'] ?? false) : false;

            return [
                'ok'     => $res->successful() && $statusFonnte,
                'status' => $res->status(),
                'body'   => $body,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => ['error' => $e->getMessage()]];
        }
    }

    /**
     * Satu pintu: susun rekap + kirim
     */
    public function recapAndSend(?string $target = null, int $days = 30): array
    {
        $text = $this->buildText($days);
        return $this->sendToGroup($text, $target);
    }
}
