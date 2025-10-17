<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kehadiran;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function __construct() 
    {

    }

    public function index(Request $request)
    {
        $rekap = DB::table('sesi_kegiatan as sk')
            ->select(
                'sk.id',
                'sk.session_date',
                'sk.weekday',
                DB::raw("
                    SUM(CASE WHEN skd.status IN ('hadir','terlambat') AND u.jenis_kelamin = 1 THEN 1 ELSE 0 END) AS hadir_l,
                    SUM(CASE WHEN skd.status IN ('hadir','terlambat') AND u.jenis_kelamin = 2 THEN 1 ELSE 0 END) AS hadir_p,
                    SUM(CASE WHEN skd.status = 'izin' AND u.jenis_kelamin = 1 THEN 1 ELSE 0 END) AS izin_l,
                    SUM(CASE WHEN skd.status = 'izin' AND u.jenis_kelamin = 2 THEN 1 ELSE 0 END) AS izin_p,
                    SUM(CASE WHEN skd.status = 'tidak_hadir' AND u.jenis_kelamin = 1 THEN 1 ELSE 0 END) AS tidak_hadir_l,
                    SUM(CASE WHEN skd.status = 'tidak_hadir' AND u.jenis_kelamin = 2 THEN 1 ELSE 0 END) AS tidak_hadir_p
                ")
            )
            ->leftJoin('sesi_kegiatan_detail as skd', 'skd.sesi_kegiatan_id', '=', 'sk.id')
            ->leftJoin('users as u', 'u.id', '=', 'skd.user_id')
            ->groupBy('sk.id', 'sk.session_date', 'sk.weekday')
            ->orderBy('sk.session_date', 'asc')
            ->get();

        return view('dashboard', compact('rekap'));
    }
}
