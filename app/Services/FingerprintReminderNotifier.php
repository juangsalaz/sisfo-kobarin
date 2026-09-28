<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FingerprintReminderNotifier
{
    /**
     * Menyusun teks pesan pengingat presensi.
     */
    public function buildText(): string
    {
        $lines = [];
        $lines[] = "Pesan ini dikirim otomatis oleh sistem (bot)";
        $lines[] = "Assalamualaikum, mengingatkan kepada penerobos supaya segera memasukkan data jamaah yang izin atau absen manual jamaah yang hadir sambung ke aplikasi.";
        $lines[] = "";
        $lines[] = "Data kehadiran akan direkap oleh sistem jam 23.00 dan laporan akan dikirimkan ke group pengurus pada jam 23.15";
        $lines[] = "";
        $lines[] = "Alhamdulillah jaza kumullahu khoiro";

        return implode("\n", $lines);
    }

    /**
     * Kirim teks ke grup WhatsApp via Fonnte API.
     */
    public function sendToGroup(?string $target = null): array
    {
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
