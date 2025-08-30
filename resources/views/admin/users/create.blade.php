<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Tambah User</h2></x-slot>
    <div class="p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <x-input-label value="Name" /><x-text-input name="name" class="w-full"/><x-input-error :messages="$errors->get('name')" />
            <x-input-label value="No HP" /><x-text-input type="text" name="no_hp" class="w-full"/><x-input-error :messages="$errors->get('no_hp')" />

            <hr class="my-4">
            <x-input-label value="PIN (mesin)" /><x-text-input name="pin" class="w-full"/><x-input-error :messages="$errors->get('pin')" />

            <x-primary-button class="mt-4">Simpan & Sinkron</x-primary-button>
        </form>
    </div>
</x-app-layout>
