<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\AttendanceRawEvent;
use App\Services\AttendanceNormalizer;

class FingerspotWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        if (($data['cloud_id'] == config('services.fingerspot.cloud_id') ||  $data['cloud_id'] == config('services.fingerspot.cloud_id2'))&& $data['type'] == 'attlog') {
            $pin     = $data['data']['pin'];
            $cloudId = $data['cloud_id'];
            $scanStr = $data['data']['scan'];
            $verify  = $data['data']['verify'] ?? null;
            $status  = $data['data']['status_scan'] ?? null;

            // parse ke UTC (asumsi input WIB)
            $local = Carbon::parse($scanStr, 'Asia/Jakarta');
            $utc   = $local->clone()->utc();

            $raw = AttendanceRawEvent::firstOrCreate(
                ['cloud_id'=>$cloudId,'pin'=>$pin,'event_time'=>$utc],
                [
                    'verify'      => $verify,
                    'status_scan' => $status,
                    'payload'     => $request->all(),
                    'received_at' => now(),
                ]
            );

            // panggil service normalisasi data
            app(AttendanceNormalizer::class)->normalizeRaw($raw);

            return response()->json([
                'ok'=>true,
                'id'=>$raw->id,
                'ts'=>$utc->toIso8601String()
            ],200);
        }

        return response()->json([
                'ok'=>false,
                'id'=>0,
            ], 404); 
    }
}
