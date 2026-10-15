<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Kegiatan;
use Carbon\Carbon;

class FingerprintReminderNotifier
{
    /**
     * Cek apakah kegiatan hari ini libur (atau tidak ada kegiatan).
     * Return: [bool $isLibur, string $alasan]
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
            return [true, "Hari {$weekday} bukan hari kegiatan."];
        }

        $def = Kegiatan::where('weekday', $map[$weekday])->first();

        if (!$def) {
            return [true, "Tidak ada definisi kegiatan untuk hari {$weekday}."];
        }

        if ($def->is_libur) {
            return [true, "Kegiatan hari {$weekday} diliburkan."];
        }

        return [false, ''];
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
     * Kirim teks ke grup WhatsApp via Fonnte API.
     */
    public function sendToGroup(?string $target = null): array
    {
        // Jangan kirim reminder jika kegiatan hari ini libur
        [$isLibur, $alasan] = $this->checkLiburHariIni();
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
        $text   = $this->buildText();

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
}
