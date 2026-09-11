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
            ->where('u.is_admin', '=', 0)
            ->groupBy('sk.id', 'sk.session_date', 'sk.weekday')
            ->orderBy('sk.session_date', 'desc')
            ->orderBy('sk.id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $startDate30Days = Carbon::now('Asia/Jakarta')->subDays(30)->toDateString();

        $getTopTidakHadirByGender = function ($gender) use ($startDate30Days) {
            return DB::table('users as u')
                ->select(
                    'u.id',
                    'u.name',
                    'u.jenis_kelamin',
                    'u.is_muda_mudi',
                    'u.is_usia_nikah',
                    DB::raw("SUM(CASE WHEN skd.status IN ('hadir', 'terlambat') THEN 1 ELSE 0 END) as jumlah_hadir"),
                    DB::raw("SUM(CASE WHEN skd.status = 'tidak_hadir' THEN 1 ELSE 0 END) as jumlah_tidak_hadir"),
                    DB::raw("SUM(CASE WHEN skd.status = 'izin' THEN 1 ELSE 0 END) as jumlah_izin")
                )
                ->join('sesi_kegiatan_detail as skd', 'skd.user_id', '=', 'u.id')
                ->join('sesi_kegiatan as sk', 'sk.id', '=', 'skd.sesi_kegiatan_id')
                ->where('u.is_admin', 0)
                ->where('u.is_muda_mudi', 0)
                ->where('u.jenis_kelamin', $gender)
                ->where('sk.session_date', '>=', $startDate30Days)
                ->groupBy('u.id', 'u.name', 'u.jenis_kelamin', 'u.is_muda_mudi', 'u.is_usia_nikah')
                ->orderByDesc('jumlah_tidak_hadir')
                ->orderBy('jumlah_hadir', 'asc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $kategori = 'Bapak-bapak';
                    if ($item->jenis_kelamin == 2) {
                        $kategori = 'Ibu-ibu';
                    }
                    if ($item->is_usia_nikah) {
                        $kategori = ($item->jenis_kelamin == 1) ? 'Mas (Usia Nikah)' : 'Mbak (Usia Nikah)';
                    } elseif ($item->is_muda_mudi) {
                        $kategori = ($item->jenis_kelamin == 1) ? 'Muda-Mudi (L)' : 'Muda-Mudi (P)';
                    }
                    $item->kategori = $kategori;
                    return $item;
                });
        };

        $topTidakHadirLaki = $getTopTidakHadirByGender(1);
        $topTidakHadirPerempuan = $getTopTidakHadirByGender(2);

        return view('dashboard', compact('rekap', 'topTidakHadirLaki', 'topTidakHadirPerempuan'));
    }

    public function detail(Request $request)
    {
        $sesiId = $request->input('sesi_id');
        $status = $request->input('status'); // 'hadir', 'izin', 'tidak_hadir'
        $gender = $request->input('gender'); // 1 (L), 2 (P)

        $sesi = DB::table('sesi_kegiatan')->where('id', $sesiId)->first();
        if (!$sesi) {
            return response()->json(['error' => 'Sesi tidak ditemukan'], 404);
        }

        $query = DB::table('sesi_kegiatan_detail as skd')
            ->join('users as u', 'u.id', '=', 'skd.user_id')
            ->where('skd.sesi_kegiatan_id', $sesiId)
            ->where('u.is_admin', 0);

        if ($status === 'hadir') {
            $query->whereIn('skd.status', ['hadir', 'terlambat']);
        } elseif ($status === 'izin') {
            $query->where('skd.status', 'izin');
        } elseif ($status === 'tidak_hadir') {
            $query->where('skd.status', 'tidak_hadir');
        }

        if ($gender) {
            $query->where('u.jenis_kelamin', $gender);
        }

        $users = $query->select(
            'u.id',
            'u.name',
            'u.no_hp',
            'u.jenis_kelamin',
            'u.is_muda_mudi',
            'u.is_usia_nikah',
            'skd.check_in',
            'skd.late_minutes',
            'skd.status'
        )
        ->orderBy('u.name', 'asc')
        ->get()
        ->map(function ($item) {
            $kategori = 'Bapak-bapak';
            if ($item->jenis_kelamin == 2) {
                $kategori = 'Ibu-ibu';
            }
            if ($item->is_usia_nikah) {
                $kategori = ($item->jenis_kelamin == 1) ? 'Mas (Usia Nikah)' : 'Mbak (Usia Nikah)';
            } elseif ($item->is_muda_mudi) {
                $kategori = ($item->jenis_kelamin == 1) ? 'Muda-Mudi (L)' : 'Muda-Mudi (P)';
            }

            $checkInFormatted = $item->check_in ? Carbon::parse($item->check_in)->format('H:i') . ' WIB' : '-';
            $keterangan = '-';
            if ($item->status === 'terlambat') {
                $keterangan = 'Terlambat ' . ($item->late_minutes ?? 0) . ' mnt';
            } elseif ($item->status === 'hadir') {
                $keterangan = 'Tepat Waktu';
            } elseif ($item->status === 'izin') {
                $keterangan = 'Izin';
            } else {
                $keterangan = 'Tidak Hadir';
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'no_hp' => $item->no_hp ?? '-',
                'kategori' => $kategori,
                'status' => $item->status,
                'check_in' => $checkInFormatted,
                'keterangan' => $keterangan,
            ];
        });

        $dateFormatted = Carbon::parse($sesi->session_date)->format('d M Y');
        $genderText = $gender == 1 ? 'Laki-laki' : ($gender == 2 ? 'Perempuan' : 'Semua');
        $statusText = $status === 'hadir' ? 'Hadir' : ($status === 'izin' ? 'Izin' : 'Tidak Hadir');

        return response()->json([
            'title' => "Daftar Jamaah $statusText ($genderText) - " . strtoupper($sesi->weekday) . ", $dateFormatted",
            'users' => $users,
            'total' => $users->count(),
        ]);
    }
}
