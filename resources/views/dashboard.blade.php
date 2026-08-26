<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-100 font-semibold text-lg">
                    Rekap Kehadiran Pengajian
                </div>
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse border border-gray-200" style="width: 100%;">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr class="border-b">
                                <th class="p-2 border border-gray-200 text-center">No</th>
                                <th class="p-2 border border-gray-200 text-center">Hari / Tanggal Kegiatan</th>
                                <th colspan="2" class="p-2 border border-gray-200 text-center bg-green-50">Hadir</th>
                                <th colspan="2" class="p-2 border border-gray-200 text-center bg-yellow-50">Izin</th>
                                <th colspan="2" class="p-2 border border-gray-200 text-center bg-red-50">Tidak Hadir</th>
                            </tr>
                            <tr class="border-b">
                                <th class="p-2 border border-gray-200"></th>
                                <th class="p-2 border border-gray-200"></th>
                                <th class="p-2 border border-gray-200 text-center bg-green-50">L</th>
                                <th class="p-2 border border-gray-200 text-center bg-green-50">P</th>
                                <th class="p-2 border border-gray-200 text-center bg-yellow-50">L</th>
                                <th class="p-2 border border-gray-200 text-center bg-yellow-50">P</th>
                                <th class="p-2 border border-gray-200 text-center bg-red-50">L</th>
                                <th class="p-2 border border-gray-200 text-center bg-red-50">P</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekap as $i => $r)
                                <tr class="border-b hover:bg-gray-50" style="height: 50px;">
                                    <td class="p-2 border border-gray-200 text-center">{{ $rekap->firstItem() + $i }}</td>
                                    <td class="p-2 border border-gray-200 text-center font-medium">
                                        {{ strtoupper($r->weekday) }} /
                                        {{ \Carbon\Carbon::parse($r->session_date)->format('d M Y') }}
                                    </td>
                                    <td class="p-2 border border-gray-200 text-center text-green-700 font-semibold">{{ $r->hadir_l }}</td>
                                    <td class="p-2 border border-gray-200 text-center text-green-700 font-semibold">{{ $r->hadir_p }}</td>
                                    <td class="p-2 border border-gray-200 text-center text-yellow-700 font-semibold">{{ $r->izin_l }}</td>
                                    <td class="p-2 border border-gray-200 text-center text-yellow-700 font-semibold">{{ $r->izin_p }}</td>
                                    <td class="p-2 border border-gray-200 text-center text-red-700 font-semibold">{{ $r->tidak_hadir_l }}</td>
                                    <td class="p-2 border border-gray-200 text-center text-red-700 font-semibold">{{ $r->tidak_hadir_p }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-4 text-center text-gray-500">Belum ada data kehadiran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $rekap->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
