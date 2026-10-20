<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Kegiatan;
use App\Models\Kehadiran;
use App\Models\User;
use Carbon\Carbon;

class FingerprintReminderNotifier
{
    /**
     * Cek apakah kegiatan hari ini libur (atau tidak ada kegiatan).
     * Return: [bool $isLibur, string $alasan, ?Kegiatan $def]
     */
    public function checkLiburHariIni(): array
    {
        $today   = Carbon::now('Asia/Jakarta');
        $weekday = strtolower($today->englishDayOfWeek);

        $map = [
            'monday'    => 'mon',
            'wednesday' => 'wed',
            'thursday'  => 'thu',
            'friday'    => 'fri',
        ];

        if (!isset($map[$weekday])) {
            return [true, "Hari {$weekday} bukan hari kegiatan.", null];
        }

        $def = Kegiatan::where('weekday', $map[$weekday])->first();

        if (!$def) {
            return [true, "Tidak ada definisi kegiatan untuk hari {$weekday}.", null];
        }

        if ($def->is_libur) {
            return [true, "Kegiatan hari {$weekday} diliburkan.", $def];
        }

        return [false, '', $def];
    }

    /**
     * Ambil daftar jamaah yang belum absen (fingerprint / manual / izin) hari ini.
     */
    public function getUnattendedUsers(?Kegiatan $def = null)
    {
        $today   = Carbon::now('Asia/Jakarta');
        $dateStr = $today->toDateString();

        if (!$def) {
            [, , $def] = $this->checkLiburHariIni();
        }

        // Ambil user_id yang sudah tercatat di tabel kehadiran hari ini
        $attendedUserIds = Kehadiran::whereDate('local_time', $dateStr)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        $query = User::where('is_admin', 0)
            ->whereNotIn('id', $attendedUserIds);

        // Jika kegiatan bukan gabungan, kecualikan muda-mudi
        if (!$def || $def->is_gabungan != 1) {
            $query->where('is_muda_mudi', 0);
        }

        return $query->orderBy('jenis_kelamin', 'asc')
            ->orderBy('is_muda_mudi', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($user) {
                if ($user->is_muda_mudi == 1) {
                    $user->panggilan_nama = $user->name;
                } elseif ($user->jenis_kelamin == 2) {
                    $panggilan = $user->is_usia_nikah == 1 ? 'Mbak' : 'Ibu';
                    $user->panggilan_nama = "{$panggilan} {$user->name}";
                } else {
                    $panggilan = $user->is_usia_nikah == 1 ? 'Mas' : 'Bpk.';
                    $user->panggilan_nama = "{$panggilan} {$user->name}";
                }

                return $user;
            });
    }

    /**
     * Menyusun teks pesan pengingat presensi.
     */
    public function buildText(): string
    {
        $lines = [];
        $lines[] = "🤖 Pesan ini dikirim secara otomatis oleh sistem (bot).";
        $lines[] = "";
        $lines[] = "Assalamualaikum, mengingatkan kepada penerobos supaya segera memasukkan data jamaah yang izin atau absen manual jamaah yang hadir sambung ke aplikasi.";
        $lines[] = "";
        $lines[] = "Data kehadiran akan direkap oleh sistem jam 21.00 dan laporan akan dikirimkan ke group pengurus pada jam 21.15";
        $lines[] = "";
        $lines[] = "Alhamdulillah jaza kumullahu khoiro";

        return implode("\n", $lines);
    }

    /**
     * Menyusun teks daftar jamaah yang belum absen (dipisah Laki-laki dan Perempuan).
     */
    public function buildUnattendedListText(?Kegiatan $def = null): string
    {
        if (!$def) {
            [, , $def] = $this->checkLiburHariIni();
        }

        $users = $this->getUnattendedUsers($def);
        $today = Carbon::now('Asia/Jakarta');
        $hari  = $today->translatedFormat('l');
        $tgl   = $today->translatedFormat('d M Y');

        $lines = [];
        $lines[] = "🤖 *Daftar Jamaah Belum Absen / Belum Input Izin / Tidak Hadir*";
        $lines[] = "📅 {$hari}, {$tgl}";

        if ($users->isEmpty()) {
            $lines[] = "";
            $lines[] = "✅ Alhamdulillah, seluruh jamaah sudah tercatat presensi/izinnya.";
            return implode("\n", $lines);
        }

        $laki      = $users->where('jenis_kelamin', 1)->values();
        $perempuan = $users->where('jenis_kelamin', 2)->values();

        if ($laki->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "👨 *Laki-laki ({$laki->count()}):*";
            foreach ($laki as $idx => $u) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$u->panggilan_nama}";
            }
        }

        if ($perempuan->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "🧕🏼 *Perempuan ({$perempuan->count()}):*";
            foreach ($perempuan as $idx => $u) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$u->panggilan_nama}";
            }
        }

        $lines[] = "";
        $lines[] = "💡 *Cara Input via WA:*";
        $lines[] = "Copy-paste daftar di atas, lalu tambahkan tanda di samping nama:";
        $lines[] = "• ✅ = Hadir";
        $lines[] = "• 🙏 = Izin";

        return implode("\n", $lines);
    }

    /**
     * Helper kirim pesan ke Fonnte API.
     */
    protected function sendFonnteMessage(string $text, string $target, string $token): array
    {
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
     * Kirim teks pengingat beserta daftar jamaah yang belum absen ke grup WhatsApp via Fonnte API.
     */
    public function sendToGroup(?string $target = null): array
    {
        // Jangan kirim reminder jika kegiatan hari ini libur
        [$isLibur, $alasan, $def] = $this->checkLiburHariIni();
        if ($isLibur) {
            return [
                'ok'      => true,
                'skipped' => true,
                'status'  => 200,
                'body'    => "{$alasan} Reminder tidak dikirim.",
            ];
        }

        $token  = (string) config('services.fonnte.token');
        $target = $target ?: (string) config('services.fonnte.absent_group_target');

        // 1. Kirim pesan pengingat pertama
        $reminderText   = $this->buildText();
        $reminderResult = $this->sendFonnteMessage($reminderText, $target, $token);

        if (!$reminderResult['ok']) {
            return $reminderResult;
        }

        // 2. Kirim pesan kedua berisi daftar user yang belum absen ke grup yang sama
        $unattendedText = $this->buildUnattendedListText($def);
        $listResult     = $this->sendFonnteMessage($unattendedText, $target, $token);

        return [
            'ok'          => $reminderResult['ok'] && $listResult['ok'],
            'status'      => $listResult['status'] ?: $reminderResult['status'],
            'body'        => $listResult['body'],
            'reminder'    => $reminderResult,
            'unattended'  => $listResult,
        ];
    }
}

