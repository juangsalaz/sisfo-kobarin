<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Tambah User</h2></x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white shadow" style="margin-top: 30px; padding: 30px;">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            {{-- Nama --}}
            <x-input-label value="Name" />
            <x-text-input name="name" class="w-full" required />
            <x-input-error :messages="$errors->get('name')" />
            <br /><br />

            {{-- Jenis Kelamin --}}
            <x-input-label value="Jenis Kelamin" />
            <select name="jenis_kelamin" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="1">Laki-laki</option>
                <option value="2">Perempuan</option>
            </select>
            <x-input-error :messages="$errors->get('jenis_kelamin')" />
            <br /><br />

            {{-- Kategori --}}
            <x-input-label value="Kategori" />
            <select name="kategori" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="bapak_ibu">Bapak/Ibu</option>
                <option value="muda_mudi">Muda-Mudi</option>
                <option value="usia_pra_nikah">Usia Pra Nikah</option>
            </select>
            <x-input-error :messages="$errors->get('kategori')" />
            <br /><br />

            {{-- No HP --}}
            <x-input-label value="No HP" />
            <x-text-input type="text" name="no_hp" class="w-full" value="0" />
            <x-input-error :messages="$errors->get('no_hp')" />
            <br /><br />

            {{-- PIN --}}
            <x-input-label value="PIN (mesin)" />
            <x-text-input name="pin" class="w-full" value="{{ $nextPin }}" />
            <x-input-error :messages="$errors->get('pin')" />

            {{-- Tombol --}}
            <div class="mt-6">
                <x-primary-button class="mt-4">Simpan & Sinkron</x-primary-button>
                <a href="{{ route('admin.users.index') }}" class="ml-2 text-lg text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
