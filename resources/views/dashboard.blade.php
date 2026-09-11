<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        showModal: false,
        loading: false,
        modalTitle: '',
        users: [],
        searchQuery: '',
        get filteredUsers() {
            if (!this.searchQuery) return this.users;
            const q = this.searchQuery.toLowerCase();
            return this.users.filter(u => 
                (u.name && u.name.toLowerCase().includes(q)) || 
                (u.kategori && u.kategori.toLowerCase().includes(q)) || 
                (u.no_hp && u.no_hp.toLowerCase().includes(q))
            );
        },
        openDetail(sesiId, status, gender) {
            this.showModal = true;
            this.loading = true;
            this.modalTitle = 'Memuat data...';
            this.users = [];
            this.searchQuery = '';
            fetch(`/dashboard/detail?sesi_id=${sesiId}&status=${status}&gender=${gender}`)
                .then(res => res.json())
                .then(data => {
                    this.modalTitle = data.title;
                    this.users = data.users || [];
                    this.loading = false;
                })
                .catch(err => {
                    this.modalTitle = 'Gagal memuat data';
                    this.loading = false;
                });
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-100 font-semibold text-lg flex justify-between items-center">
                    <span>Rekap Kehadiran Pengajian</span>
                    <span class="text-xs font-normal text-gray-500">*Klik angka jumlah kehadiran untuk melihat daftar nama jamaah</span>
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
                                    <!-- Hadir L -->
                                    <td class="p-2 border border-gray-200 text-center">
                                        @if($r->hadir_l > 0)
                                            <button type="button" @click="openDetail({{ $r->id }}, 'hadir', 1)" class="w-full font-semibold text-green-700 hover:underline cursor-pointer hover:bg-green-100 py-1 rounded transition">
                                                {{ $r->hadir_l }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 font-normal">0</span>
                                        @endif
                                    </td>
                                    <!-- Hadir P -->
                                    <td class="p-2 border border-gray-200 text-center">
                                        @if($r->hadir_p > 0)
                                            <button type="button" @click="openDetail({{ $r->id }}, 'hadir', 2)" class="w-full font-semibold text-green-700 hover:underline cursor-pointer hover:bg-green-100 py-1 rounded transition">
                                                {{ $r->hadir_p }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 font-normal">0</span>
                                        @endif
                                    </td>
                                    <!-- Izin L -->
                                    <td class="p-2 border border-gray-200 text-center">
                                        @if($r->izin_l > 0)
                                            <button type="button" @click="openDetail({{ $r->id }}, 'izin', 1)" class="w-full font-semibold text-yellow-700 hover:underline cursor-pointer hover:bg-yellow-100 py-1 rounded transition">
                                                {{ $r->izin_l }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 font-normal">0</span>
                                        @endif
                                    </td>
                                    <!-- Izin P -->
                                    <td class="p-2 border border-gray-200 text-center">
                                        @if($r->izin_p > 0)
                                            <button type="button" @click="openDetail({{ $r->id }}, 'izin', 2)" class="w-full font-semibold text-yellow-700 hover:underline cursor-pointer hover:bg-yellow-100 py-1 rounded transition">
                                                {{ $r->izin_p }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 font-normal">0</span>
                                        @endif
                                    </td>
                                    <!-- Tidak Hadir L -->
                                    <td class="p-2 border border-gray-200 text-center">
                                        @if($r->tidak_hadir_l > 0)
                                            <button type="button" @click="openDetail({{ $r->id }}, 'tidak_hadir', 1)" class="w-full font-semibold text-red-700 hover:underline cursor-pointer hover:bg-red-100 py-1 rounded transition">
                                                {{ $r->tidak_hadir_l }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 font-normal">0</span>
                                        @endif
                                    </td>
                                    <!-- Tidak Hadir P -->
                                    <td class="p-2 border border-gray-200 text-center">
                                        @if($r->tidak_hadir_p > 0)
                                            <button type="button" @click="openDetail({{ $r->id }}, 'tidak_hadir', 2)" class="w-full font-semibold text-red-700 hover:underline cursor-pointer hover:bg-red-100 py-1 rounded transition">
                                                {{ $r->tidak_hadir_p }}
                                            </button>
                                        @else
                                            <span class="text-gray-400 font-normal">0</span>
                                        @endif
                                    </td>
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

            <!-- Grid 10 Jamaah Paling Jarang Hadir (Laki-laki & Perempuan) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <!-- Tabel 10 Laki-laki -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5 text-gray-900 border-b border-gray-100 font-semibold text-base flex justify-between items-center bg-blue-50/50">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-600 text-white">Laki-laki</span>
                            <span>10 Paling Jarang Hadir (30 Hari)</span>
                        </div>
                    </div>
                    <div class="p-4 overflow-x-auto">
                        <table class="min-w-full text-xs border-collapse border border-gray-200" style="width: 100%;">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr class="border-b">
                                    <th class="p-2 border border-gray-200 text-center w-8">No</th>
                                    <th class="p-2 border border-gray-200 text-left">Nama Jamaah</th>
                                    <th class="p-2 border border-gray-200 text-left">Kategori</th>
                                    <th class="p-2 border border-gray-200 text-center bg-green-50">Hadir</th>
                                    <th class="p-2 border border-gray-200 text-center bg-red-50">Tdk Hadir</th>
                                    <th class="p-2 border border-gray-200 text-center bg-yellow-50">Izin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topTidakHadirLaki as $idx => $user)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-2 border border-gray-200 text-center font-medium text-gray-500">{{ $idx + 1 }}</td>
                                        <td class="p-2 border border-gray-200 font-semibold text-gray-900">{{ $user->name }}</td>
                                        <td class="p-2 border border-gray-200 text-[11px]">
                                            <span class="px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-700">
                                                {{ $user->kategori }}
                                            </span>
                                        </td>
                                        <td class="p-2 border border-gray-200 text-center font-semibold text-green-700 bg-green-50/50">
                                            {{ $user->jumlah_hadir }}
                                        </td>
                                        <td class="p-2 border border-gray-200 text-center font-bold text-red-700 bg-red-50">
                                            {{ $user->jumlah_tidak_hadir }}
                                        </td>
                                        <td class="p-2 border border-gray-200 text-center font-semibold text-yellow-700 bg-yellow-50/50">
                                            {{ $user->jumlah_izin }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-4 text-center text-gray-500">Belum ada data 30 hari terakhir.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel 10 Perempuan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5 text-gray-900 border-b border-gray-100 font-semibold text-base flex justify-between items-center bg-pink-50/50">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-pink-600 text-white">Perempuan</span>
                            <span>10 Paling Jarang Hadir (30 Hari)</span>
                        </div>
                    </div>
                    <div class="p-4 overflow-x-auto">
                        <table class="min-w-full text-xs border-collapse border border-gray-200" style="width: 100%;">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr class="border-b">
                                    <th class="p-2 border border-gray-200 text-center w-8">No</th>
                                    <th class="p-2 border border-gray-200 text-left">Nama Jamaah</th>
                                    <th class="p-2 border border-gray-200 text-left">Kategori</th>
                                    <th class="p-2 border border-gray-200 text-center bg-green-50">Hadir</th>
                                    <th class="p-2 border border-gray-200 text-center bg-red-50">Tdk Hadir</th>
                                    <th class="p-2 border border-gray-200 text-center bg-yellow-50">Izin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topTidakHadirPerempuan as $idx => $user)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-2 border border-gray-200 text-center font-medium text-gray-500">{{ $idx + 1 }}</td>
                                        <td class="p-2 border border-gray-200 font-semibold text-gray-900">{{ $user->name }}</td>
                                        <td class="p-2 border border-gray-200 text-[11px]">
                                            <span class="px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-700">
                                                {{ $user->kategori }}
                                            </span>
                                        </td>
                                        <td class="p-2 border border-gray-200 text-center font-semibold text-green-700 bg-green-50/50">
                                            {{ $user->jumlah_hadir }}
                                        </td>
                                        <td class="p-2 border border-gray-200 text-center font-bold text-red-700 bg-red-50">
                                            {{ $user->jumlah_tidak_hadir }}
                                        </td>
                                        <td class="p-2 border border-gray-200 text-center font-semibold text-yellow-700 bg-yellow-50/50">
                                            {{ $user->jumlah_izin }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-4 text-center text-gray-500">Belum ada data 30 hari terakhir.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="showModal" 
             x-cloak 
             @keydown.escape.window="showModal = false"
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

            <!-- Modal Box -->
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                    
                    <!-- Modal Header -->
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-base font-bold text-gray-900" x-text="modalTitle"></h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none text-2xl font-semibold leading-none">&times;</button>
                    </div>

                    <!-- Search & Info Bar -->
                    <div class="px-6 py-3 bg-gray-100 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-2" x-show="!loading && users.length > 0">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Total: <span x-text="users.length" class="text-gray-900 font-bold"></span> Jamaah
                        </span>
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Cari nama atau kategori..." 
                               class="text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full sm:w-64">
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 max-h-96 overflow-y-auto">
                        <!-- Loading State -->
                        <div x-show="loading" class="flex flex-col items-center justify-center py-8">
                            <svg class="animate-spin h-8 w-8 text-indigo-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-500 font-medium">Mengambil daftar nama jamaah...</span>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && users.length === 0" class="text-center py-8 text-gray-500 text-sm">
                            Tidak ada data jamaah untuk kategori ini.
                        </div>

                        <!-- User Table -->
                        <div x-show="!loading && users.length > 0">
                            <table class="min-w-full text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Jamaah</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status / Jam Scan</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No HP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template x-for="(u, idx) in filteredUsers" :key="u.id">
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-xs text-gray-500" x-text="idx + 1"></td>
                                            <td class="px-3 py-2 font-semibold text-gray-900" x-text="u.name"></td>
                                            <td class="px-3 py-2 text-xs">
                                                <span class="px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-800" x-text="u.kategori"></span>
                                            </td>
                                            <td class="px-3 py-2 text-xs text-center">
                                                <template x-if="u.status === 'hadir'">
                                                    <span class="px-2 py-0.5 rounded-md font-semibold bg-green-100 text-green-800" x-text="u.check_in"></span>
                                                </template>
                                                <template x-if="u.status === 'terlambat'">
                                                    <span class="px-2 py-0.5 rounded-md font-semibold bg-yellow-100 text-yellow-800" x-text="u.check_in + ' (' + u.keterangan + ')'"></span>
                                                </template>
                                                <template x-if="u.status === 'izin'">
                                                    <span class="px-2 py-0.5 rounded-md font-semibold bg-blue-100 text-blue-800">Izin</span>
                                                </template>
                                                <template x-if="u.status === 'tidak_hadir'">
                                                    <span class="px-2 py-0.5 rounded-md font-semibold bg-red-100 text-red-800">Tidak Hadir</span>
                                                </template>
                                            </td>
                                            <td class="px-3 py-2 text-xs text-gray-600" x-text="u.no_hp"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 hover:bg-gray-50 focus:outline-none">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
