<!-- resources/views/admin/users/index.blade.php -->
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Users</h2></x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white shadow" style="margin-top: 30px;">
        <div style="padding-top: 25px; padding-bottom: 25px;">
            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                <input type="text" name="search" placeholder="Cari user..." value="{{ request('search') }}" />
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Search</button>
            </form>
            <div style="text-align: right;">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Tambah User</a>
            </div>
            @if(session('status')) <div class="mt-4 text-green-600">{{ session('status') }}</div> @endif

            <table class="mt-4 w-full border">
                <thead><tr>
                    <th class="p-2 border">Nama</th>
                    <th class="p-2 border">No HP</th>
                    <th class="p-2 border">PIN</th>
                    <th class="p-2 border">Aksi</th>
                </tr></thead>
                <tbody>
                @foreach($users as $u)
                    <tr>
                        <td class="p-2 border">{{ $u->name }}</td>
                        <td class="p-2 border">{{ $u->no_hp }}</td>
                        <td class="p-2 border">{{ $u->pin }}</td>
                        <td class="p-2 border">
                            @if ($u->is_admin == 0)
                                <a href="{{ route('admin.users.edit',$u) }}" class="underline">Edit</a>
                                <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus user?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 underline ml-2">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
