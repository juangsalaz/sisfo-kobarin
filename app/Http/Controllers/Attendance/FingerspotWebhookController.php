<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\AttendanceRawEvent;

class FingerspotWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Payload contoh:
        // {"type":"attlog","cloud_id":"XXXX","data":{"pin":"1","scan":"2020-07-21 10:11","verify":"1","status_scan":"1"}}

        $data = $request->validate([
            'type'             => ['required','in:attlog'],
            'cloud_id'         => ['required','string'],
            'data.pin'         => ['required','string'],
            'data.scan'        => ['required','string'],
            'data.verify'      => ['nullable','string'],
            'data.status_scan' => ['nullable','string'],
        ]);

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

        return response()->json([
            'ok'=>true,
            'id'=>$raw->id,
            'ts'=>$utc->toIso8601String()
        ],200);
    }
}
