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

class UserController extends Controller
{
    public function __construct() {
    }

    public function index() {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create() {
        return view('admin.users.create');
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

    public function edit(User $user) {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user) {
        $data = $request->validated();
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->fill([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'pin'         => $data['pin'],
            'privilege'   => $data['privilege'],
            'fp_password' => $data['fp_password'] ?? null,
            'rfid'        => $data['rfid'] ?? null,
            'fp_template' => $data['fp_template'] ?? null,
        ])->save();

        SyncUserToFingerspot::dispatch($user);

        return redirect()->route('admin.users.index')->with('status', 'User diupdate & dikirim ke mesin.');
    }

    public function destroy(User $user) {
        // Hapus lokal. (Untuk hapus di mesin, cek dulu apakah ada endpoint khusus delete di API.)
        $user->delete();
        return back()->with('status', 'User dihapus (lokal).');
    }
}
