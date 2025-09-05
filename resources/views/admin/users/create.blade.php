<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Tambah User</h2></x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white shadow" style="margin-top: 30px; padding: 30px;">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <x-input-label value="Name" /><x-text-input name="name" class="w-full" required/><x-input-error :messages="$errors->get('name')" />
            <br /> <br />
            <x-input-label value="No HP" /><x-text-input type="text" name="no_hp" class="w-full" value="0"/><x-input-error :messages="$errors->get('no_hp')" />
            <br /> <br />
            <x-input-label value="PIN (mesin)" /><x-text-input name="pin" class="w-full" value="{{ $nextPin }}" /><x-input-error :messages="$errors->get('pin')" />

            <div class="mt-6">
                <x-primary-button class="mt-4">Simpan & Sinkron</x-primary-button>
                <a href="{{ route('admin.users.index') }}" class="ml-2 text-lg text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
