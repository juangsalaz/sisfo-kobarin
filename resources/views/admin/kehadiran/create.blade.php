<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Absen Manual</h2></x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white shadow" style="margin-top: 30px; padding: 30px;">
        <div class="max-w-lg mx-auto mt-6">
        
        <form method="POST" action="{{ route('kehadiran.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="tanggal" class="block mb-1">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal"
                    value="{{ old('tanggal', now('Asia/Jakarta')->toDateString()) }}"
                    class="w-full border rounded p-2" required>
                @error('tanggal')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>

            <div style="margin-top:10px;">
                <label for="user_id" class="block mb-1">Nama User</label>
                <select name="user_id" id="user_id" class="w-full border rounded p-2 select2" style="width: 100%;" required>
                    <option value="">-- Pilih User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="flex items-center space-x-2" style="margin-top:10px;">
            <input type="checkbox" name="is_izin" id="is_izin" value="1">
            <label for="is_izin">Izin?</label>
            </div>

            <button type="submit" style="margin-top:10px;" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Simpan
            </button>
        </form>
        </div>
    </div>
</x-app-layout>

<!-- jQuery harus dimuat sebelum Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- CSS & JS Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#user_id').select2({
            placeholder: "Pilih user",
            allowClear: true
        });
    });
</script>
