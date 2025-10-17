<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Selamat datang di SISFO Kota Barat
                </div>

                <div class="p-6 text-gray-900">
                    Rekap Kehadiran Pengajian
                </div>
                <table class="min-w-full text-sm" style="width: 100%;">
                    <thead class="table-light">
                        <tr class="border-b">
                            <th>No</th>
                            <th>Hari / Tanggal Kegiatan</th>
                            <th colspan="2">Hadir</th>
                            <th colspan="2">Izin</th>
                            <th colspan="2">Tidak Hadir</th>
                        </tr>
                        <tr class="border-b">
                            <th></th>
                            <th></th>
                            <th>L</th>
                            <th>P</th>
                            <th>L</th>
                            <th>P</th>
                            <th>L</th>
                            <th>P</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap as $i => $r)
                            <tr class="border-b" style="height: 50px;">
                                <td style="text-align: center;">{{ $i + 1 }}</td>
                                <td style="text-align: center;">
                                    {{ strtoupper($r->weekday) }} /
                                    {{ \Carbon\Carbon::parse($r->session_date)->format('d M Y') }}
                                </td>
                                <td style="text-align: center;">{{ $r->hadir_l }}</td>
                                <td style="text-align: center;">{{ $r->hadir_p }}</td>
                                <td style="text-align: center;">{{ $r->izin_l }}</td>
                                <td style="text-align: center;">{{ $r->izin_p }}</td>
                                <td style="text-align: center;">{{ $r->tidak_hadir_l }}</td>
                                <td style="text-align: center;">{{ $r->tidak_hadir_p }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada data kehadiran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-app-layout>
