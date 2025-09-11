<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kehadiran;
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    public function __construct() 
    {

    }

    public function index(Request $request)
    {
        $tanggal = $request->query('tanggal_kegiatan'); // format: YYYY-MM-DD

        $query = Kehadiran::query()->with(['user:id,name'])->orderBy('local_time');

        // filter hanya jika tanggal ada
        if ($tanggal) {
            $request->validate([
                'tanggal_kegiatan' => ['date'], // validasi sederhana
            ]);

            $query->whereDate('local_time', $tanggal);
        } else {
            // kalau ingin kosong sebelum search, pakai whereRaw('1=0')
            $query->whereRaw('1=0');
            // atau: default ke hari ini
            // $query->whereDate('local_time', now()->toDateString());
        }

        // pagination + keep query string
        $kehadiran = $query->paginate(50)->withQueryString();

        return view('admin.kehadiran.index', [
            'kehadiran' => $kehadiran,
            'tanggal'   => $tanggal,
        ]);
    }

}
