<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FingerspotService
{
    public function setUserInfo(User $user): array
    {
        $cloudId = config('services.fingerspot.cloud_id');
        if ($user->jenis_kelamin == 2) {
            $cloudId = config('services.fingerspot.cloud_id2');
        }

        $payload = [
            'trans_id' => (string) Str::uuid(),
            'cloud_id' => $cloudId,
            'data' => [
                'pin'       => (string) $user->pin,
                'name'      => (string) $user->name,
                'privilege' => (string) $user->privilege,
                'password'  => (string) ($user->fp_password ?? ''),
                'rfid'      => (string) ($user->rfid ?? ''),
                'template'  => (string) ($user->fp_template ?? ''),
            ],
        ];

        $resp = Http::asJson()
            ->withToken(config('services.fingerspot.token'))
            ->timeout(15)
            ->post(config('services.fingerspot.endpoint'), $payload);

        $ok = $resp->successful();
        $body = $resp->json() ?? ['raw' => $resp->body()];

        Log::info('Fingerspot set_userinfo', [
            'ok'=>$ok,
            'payload'=>$payload,
            'response'=>$body
        ]);

        return ['ok'=>$ok, 'body'=>$body];
    }

    public function deleteUserInfo(User $user): array
    {
        $payload = [
            'trans_id' => (string) Str::uuid(),
            'cloud_id' => config('services.fingerspot.cloud_id'),
            'pin'      => (string) $user->pin,
        ];

        $resp = Http::asJson()
            ->withToken(config('services.fingerspot.token'))
            ->timeout(15)
            ->post(config('services.fingerspot.endpoint_delete_user'), $payload);

        $ok = $resp->successful();
        $body = $resp->json() ?? ['raw' => $resp->body()];

        Log::info('Fingerspot delete_userinfo', [
            'ok'=>$ok,
            'payload'=>$payload,
            'response'=>$body
        ]);

        return ['ok'=>$ok, 'body'=>$body];
    }
}
