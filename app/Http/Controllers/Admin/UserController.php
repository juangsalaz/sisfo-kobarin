<?php

// app/Http/Controllers/Admin/UserController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\SyncUserToFingerspot;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\FingerspotService;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    public function __construct() {
    }

    public function index(Request $request) {
        $query = \App\Models\User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('pin', 'like', "%{$search}%");
            });
        }

        $users = $query->where('is_admin', 0)->orderBy('name')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create() {
        $nextPin = \App\Models\User::selectRaw('COALESCE(MAX(CAST(pin AS UNSIGNED)), 0) + 1 AS next_pin')
            ->value('next_pin');

        return view('admin.users.create', compact('nextPin'));
    }

    public function store(UserStoreRequest $request, FingerspotService $svc) {
        $data = $request->validated();

        $user = User::create([
            'name'        => $data['name'],
            'email'       => '',
            'no_hp'       => $data['no_hp'],
            'password'    => Hash::make('password123'),
            'pin'         => $data['pin'],
            'privilege'   => 0,
            'fp_password' => null,
            'rfid'        => null,
            'fp_template' =>null,
        ]);

        $res = $svc->setUserInfo($user);

        $user->forceFill([
            'synced_at'       => now(),
            'last_sync_status'=> $res['ok'] ? 'success' : 'failed',
        ])->save();

        return redirect()->route('admin.users.index')
            ->with('status', 'User dibuat & dikirim ke mesin: '.($res['ok']?'OK':'Gagal'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user, FingerspotService $svc)
    {
        $data = $request->validated();

        // field yang boleh diupdate
        $user->fill([
            'name'        => $data['name'],
            'no_hp'       => $data['no_hp'] ?? null,
            'is_admin'    => $data['is_admin'] ?? false,
            'pin'         => $data['pin'],
        ])->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Data user berhasil diperbarui'.(!empty($data['resync']) ? ' & disinkron ke mesin.' : '.'));
    }

    public function destroy(User $user, FingerspotService $svc) {
        // Hapus lokal. (Untuk hapus di mesin, cek dulu apakah ada endpoint khusus delete di API.)
        $user->delete();

        $res = $svc->deleteUserInfo($user);

        return back()->with('status', 'User dihapus (lokal).');
    }
}
