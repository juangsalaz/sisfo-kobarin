<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kehadiran;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            //$query->whereRaw('1=0');
            // atau: default ke hari ini
            $query->whereDate('local_time', now()->toDateString());
        }

        // pagination + keep query string
        $kehadiran = $query->paginate(50)->withQueryString();

        return view('admin.kehadiran.index', [
            'kehadiran' => $kehadiran,
            'tanggal'   => $tanggal,
        ]);
    }

    public function create()
    {
        $users = User::select('id', 'name')->where('is_admin', 0)->orderBy('name')->get();

        return view('admin.kehadiran.create', compact('users'));
    }

    public function store(Request $request)
    {
        // Validasi user_id wajib ada
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_izin' => 'nullable|boolean',
            'tanggal' => 'required',
        ]);

        $tanggal = \Carbon\Carbon::parse($validated['tanggal'], 'Asia/Jakarta')
            ->setTime(19, 45, 0);

        $waktu = $tanggal;

        $exists = Kehadiran::where('user_id', $validated['user_id'])
            ->whereDate('local_time', $validated['tanggal'])
            ->exists();

        if (!$exists) {
            Kehadiran::create([
                'user_id'     => $validated['user_id'],
                'event_time'  => $waktu,       // UTC time atau sesuai server
                'local_time'  => $waktu,       // waktu lokal saat submit
                'method'      => '',
                'device'      => '',
                'raw_id'      => 0,          // isi default 0 atau sesuai kebutuhan
                'is_in_session_window' => 1,
                'is_izin'     => $validated['is_izin'] ?? 0,
            ]);
        }
        
        return redirect()->route('kehadiran.index')->with('success', 'Data kehadiran berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $kehadiran = Kehadiran::findOrFail($id);
        $kehadiran->delete();

        return redirect()->route('kehadiran.index')->with('success', 'Data kehadiran berhasil dihapus.');
    }


}
