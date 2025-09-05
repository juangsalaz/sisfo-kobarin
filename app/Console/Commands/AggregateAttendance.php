<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Kegiatan;
use App\Models\SesiKegiatan;
use App\Models\Kehadiran;
use App\Models\SesiKegiatanDetail;
use Carbon\Carbon;

class AggregateAttendance extends Command
{
    protected $signature = 'attendance:aggregate {date?}';
    protected $description = 'Hitung kehadiran untuk sesi pengajian';

    public function handle()
    {
        $date = $this->argument('date') 
            ? Carbon::parse($this->argument('date'), 'Asia/Jakarta')->toDateString()
            : now('Asia/Jakarta')->toDateString();

        $weekday = strtolower(Carbon::parse($date, 'Asia/Jakarta')->englishDayOfWeek);

        if (!in_array($weekday, ['monday','thursday'])) {
            $this->info("Tanggal $date bukan hari pengajian.");
            return Command::SUCCESS;
        }

        $def = Kegiatan::where('weekday', $weekday === 'monday' ? 'mon' : 'thu')->firstOrFail();

        $startTime = $def->start_time instanceof \Carbon\CarbonInterface
            ? $def->start_time->format('H:i:s')
            : (string) $def->start_time;

        $endTime = $def->end_time instanceof \Carbon\CarbonInterface
            ? $def->end_time->format('H:i:s')
            : (string) $def->end_time;

        $start = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$startTime}", 'Asia/Jakarta');
        $end   = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$endTime}",   'Asia/Jakarta');

        $occ = SesiKegiatan::firstOrCreate(
            ['session_date' => $date, 'weekday' => $def->weekday],
            ['start_at_local'=>$start, 'end_at_local'=>$end]
        );

        $users = User::orderBy('name')->get();

        foreach ($users as $u) {
            $firstEvent = Kehadiran::where('user_id', $u->id)
                ->whereBetween('local_time', [$start, $end])
                ->orderBy('local_time','asc')
                ->first();

            if (!$firstEvent) {
                $status = 'tidak_hadir';
                $late   = 0;
                $checkIn= null;
            } else {
                $checkIn = Carbon::parse($firstEvent->local_time)->timezone('Asia/Jakarta');
                $graceEnd = (clone $start)->addMinutes((int) $def->grace_in_minutes);
                $late = $checkIn->greaterThan($graceEnd)
                    ? $graceEnd->diffInMinutes($checkIn)   // urutan benar: graceEnd → checkIn
                    : 0;

                $status = $late > 0 ? 'terlambat' : 'hadir';
            }

            SesiKegiatanDetail::updateOrCreate(
                ['sesi_kegiatan_id'=>$occ->id, 'user_id'=>$u->id],
                ['check_in'=>$checkIn, 'late_minutes'=>$late, 'status'=>$status]
            );
        }

        $this->info("Rekap kehadiran untuk $date berhasil dihitung.");
        return Command::SUCCESS;
    }
}

