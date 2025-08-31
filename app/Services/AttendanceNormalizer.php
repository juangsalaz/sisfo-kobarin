<?php

namespace App\Services;

use App\Models\User;
use App\Models\Kehadiran;
use Carbon\Carbon;

class AttendanceNormalizer
{
    public function normalizeRaw($raw)
    {
        // Cari user berdasarkan PIN
        $user = User::where('pin', $raw->pin)->first();
        if (!$user) return null;

        // Waktu local Asia/Jakarta
        $local = Carbon::parse($raw->event_time)->timezone('Asia/Jakarta');

        // Cek apakah hari Senin / Kamis, jam 19:30–21:30
        $weekday = strtolower($local->englishDayOfWeek); 
        $isPengajian = in_array($weekday, ['monday','thursday']);
        $isInWindow = false;
        $start = null; $end = null;

        if ($isPengajian) {
            $start = (clone $local)->setTime(19,30,0);
            $end   = (clone $local)->setTime(21,30,0);
            $isInWindow = $local->between($start, $end);
        }

        if ($isInWindow) {
            $exists = Kehadiran::where('user_id', $user->id)
                ->whereBetween('local_time', [$start, $end])
                ->exists();

            if ($exists) {
                return null;
            }

            // Buat event baru
            return Kehadiran::create([
                'user_id'              => $user->id,
                'event_time'           => $raw->event_time,
                'local_time'           => $local,
                'method'               => $raw->method,
                'device'               => $raw->device,
                'raw_id'               => $raw->id,
                'is_in_session_window' => $isInWindow,
            ]);
        }

        return null;
    }
}
