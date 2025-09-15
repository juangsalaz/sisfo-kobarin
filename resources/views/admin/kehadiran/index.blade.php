<style>
    .badge {
        display: inline-block;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 9999px; /* pill shape */
        color: #fff;
    }
    .badge-yellow {
        background-color: #facc15; /* kuning */
        color: #854d0e;
    }
    .badge-green {
        background-color: #22c55e; /* hijau */
        color: #14532d;
    }
</style>
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Rekap Kehadiran</h2></x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white shadow" style="margin-top: 30px; padding: 30px;">
        <div>
            <label for="tanggal-kegiatan">Tanggal Kegiatan</label>
            <form method="GET" action="{{ route('kehadiran.index') }}">
                <input type="date" name="tanggal_kegiatan" id="tanggal-kegiatan"
                        value="{{ old('tanggal_kegiatan', $tanggal) }}">
                <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cari
                </button>
            </form>

            <div style="margin-top: 20px;">
                <a href="{{ route('kehadiran.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Absen Manual</a>
            </div>
        </div>

        @if($errors->has('tanggal_kegiatan'))
        <div class="text-red-600 text-sm mt-2">{{ $errors->first('tanggal_kegiatan') }}</div>
        @endif

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm" style="width: 100%;">
                <thead>
                <tr class="border-b">
                    <th class="text-left py-2 pr-4">No</th>
                    <th class="text-left py-2 pr-4">User</th>
                    <th class="text-left py-2 pr-4">Jam Absen</th>
                    <th class="text-left py-2">Izin?</th>
                    <th class="text-left py-2">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($kehadiran as $row)
                    <tr class="border-b">
                        <td class="py-2 pr-4">{{ $loop->iteration }}</td>
                        <td class="py-2 pr-4">{{ $row->user->name ?? '—' }}</td>
                        <td class="py-2 pr-4">{{ \Illuminate\Support\Carbon::parse($row->local_time)->format('Y-m-d H:i:s') }}</td>
                        <td class="py-2">
                            @if($row->is_izin)
                                <span class="badge badge-yellow">Ya</span>
                            @else
                                <span class="badge badge-green">Tidak</span>
                            @endif
                        </td>
                        <td class="py-2 pr-4">
                            <form action="{{ route('kehadiran.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                    <td colspan="7" class="py-4 text-center text-gray-500">
                        @if($tanggal) Tidak ada data untuk tanggal {{ $tanggal }}. @else Silakan pilih tanggal lalu klik Cari. @endif
                    </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $kehadiran->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
