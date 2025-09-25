<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WaPersonalService
{
    public function sendPersonal(array $phones, string $text): array
    {
        $base = rtrim(config('services.wa.base_url'), '/');
        $path = config('services.wa.personal_path');
        $key  = config('services.wa.api_key');

        try {
            $res = Http::withHeaders([
                    'x-api-key' => $key,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(20)
                ->post($base . $path, [
                    'phones' => $phones,
                    'text'   => $text,
                ]);

            return [
                'ok'     => $res->successful(),
                'status' => $res->status(),
                'body'   => rescue(fn() => $res->json(), $res->body(), report: false),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'body' => ['error' => $e->getMessage()]];
        }
    }
}
