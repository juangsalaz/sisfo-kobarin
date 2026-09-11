<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\SesiKegiatan;
use Carbon\Carbon;

class ConsecutiveAbsentNotifier
{
    /**
     * Ambil jamaah (non muda-mudi & non admin) yang 3x pengajian berturut-turut tidak hadir.
     */
    public function get3xConsecutiveAbsentees(?string $targetDate = null)
    {
        $date = $targetDate 
            ? Carbon::parse($targetDate, 'Asia/Jakarta')->toDateString()
            : Carbon::now('Asia/Jakarta')->toDateString();

        // Ambil 3 sesi pengajian terakhir hingga tanggal $date
        $last3Sessions = SesiKegiatan::whereDate('session_date', '<=', $date)
            ->orderBy('session_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();

        if ($last3Sessions->count() < 3) {
            return [
                'sessions' => $last3Sessions,
                'users' => collect(),
            ];
        }

        $sessionIds = $last3Sessions->pluck('id')->toArray();

        // Cari user yang berada pada ketiga sesi ini DAN status di ketiga sesi tersebut adalah 'tidak_hadir'
        $absentUsers = DB::table('users as u')
            ->select(
                'u.id',
                'u.name',
                'u.no_hp',
                'u.jenis_kelamin',
                'u.is_muda_mudi',
                'u.is_usia_nikah',
                DB::raw("COUNT(CASE WHEN skd.status = 'tidak_hadir' THEN 1 END) as count_tidak_hadir")
            )
            ->join('sesi_kegiatan_detail as skd', 'skd.user_id', '=', 'u.id')
            ->whereIn('skd.sesi_kegiatan_id', $sessionIds)
            ->where('u.is_admin', 0)
            ->where('u.is_muda_mudi', 0) // Pengecualian Muda-Mudi
            ->groupBy('u.id', 'u.name', 'u.no_hp', 'u.jenis_kelamin', 'u.is_muda_mudi', 'u.is_usia_nikah')
            ->having('count_tidak_hadir', '=', 3)
            ->orderBy('u.jenis_kelamin', 'asc')
            ->orderBy('u.name', 'asc')
            ->get()
            ->map(function ($user) {
                $panggilan = 'Bpk.';
                if ($user->jenis_kelamin == 2) {
                    $panggilan = $user->is_usia_nikah == 1 ? 'Mbak' : 'Ibu';
                } else {
                    $panggilan = $user->is_usia_nikah == 1 ? 'Mas' : 'Bpk.';
                }
                $user->panggilan_nama = "{$panggilan} {$user->name}";
                return $user;
            });

        return [
            'sessions' => $last3Sessions,
            'users' => $absentUsers,
        ];
    }

    /**
     * Menyusun format pesan WhatsApp untuk jamaah 3x tidak hadir berturut-turut.
     */
    public function buildText(?string $targetDate = null): array
    {
        $data = $this->get3xConsecutiveAbsentees($targetDate);
        $sessions = $data['sessions'];
        $users = $data['users'];

        if ($sessions->count() < 3) {
            return [false, "Belum ada data 3 sesi pengajian terakhir untuk dihitung."];
        }

        if ($users->isEmpty()) {
            return [true, '', false];
        }

        // Tanggal 3 sesi terakhir (diurutkan dari lama ke baru untuk tampilan pesan)
        $datesFormatted = $sessions->reverse()->map(function ($s) {
            return Carbon::parse($s->session_date, 'Asia/Jakarta')->translatedFormat('d M Y');
        })->implode(', ');

        $lines = [];
        $lines[] = "🤖 Pesan ini dikirim otomatis dari sistem.";
        $lines[] = "";
        $lines[] = "⚠️ *Pemberitahuan Kehadiran Pengajian*";
        $lines[] = "Daftar Jamaah yang *3x Pengajian Tidak Hadir / Tidak Absen Berturut-Turut*";
        $lines[] = "📌 *(Sesi: {$datesFormatted})*";
        $lines[] = "";
        $lines[] = "📋 *Daftar Nama Jamaah:*";
        
        $laki = $users->where('jenis_kelamin', 1);
        $perempuan = $users->where('jenis_kelamin', 2);

        if ($laki->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "👨 *Jamaah Laki-laki:*";
            foreach ($laki->values() as $idx => $u) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$u->panggilan_nama}";
            }
        }

        if ($perempuan->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "🧕🏼 *Jamaah Perempuan:*";
            foreach ($perempuan->values() as $idx => $u) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$u->panggilan_nama}";
            }
        }

        $lines[] = "";
        $lines[] = "💬 *Himbauan untuk Penerobos:*";
        $lines[] = "Mohon amalsholih dari para pengurus untuk dapat mengingatkan jamaah tersebut (untuk absen atau izin ketika tidak hadir) atau menanyakan kabar/kondisi jamaah tersebut di atas";

        return [true, implode("\n", $lines), true];
    }

    /**
     * Kirim teks ke grup WhatsApp via Fonnte API.
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
     * Satu pintu: susun rekap 3x absen + kirim
     */
    public function recapAndSend(?string $targetDate = null, ?string $target = null): array
    {
        [$ok, $text, $hasUsers] = $this->buildText($targetDate);
        if (!$ok) {
            return ['ok' => false, 'status' => 0, 'body' => $text];
        }

        if (!$hasUsers) {
            return [
                'ok' => true,
                'status' => 200,
                'body' => 'Tidak ada jamaah 3x tidak hadir berturut-turut. Pesan WA tidak dikirim.',
            ];
        }

        return $this->sendToGroup($text, $target);
    }
}
