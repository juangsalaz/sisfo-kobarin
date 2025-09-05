<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit User</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white shadow" style="margin-top: 30px; padding: 30px;">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium">Nama</label>
                    <input type="text" name="name" class="mt-1 w-full border rounded p-2"
                           value="{{ old('name', $user->name) }}" required>
                </div>

                <br />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium">No HP</label>
                        <input type="text" name="no_hp" class="mt-1 w-full border rounded p-2"
                               value="{{ old('no_hp', $user->no_hp) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Admin?</label>
                        <input type="checkbox" name="is_admin" value="1" class="mt-3"
                               @checked(old('is_admin', $user->is_admin))>
                    </div>
                </div>

                <br />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium">PIN (unik)</label>
                        <input type="text" name="pin" class="mt-1 w-full border rounded p-2"
                               value="{{ old('pin', $user->pin) }}" required>
                    </div>
                </div>

                <div class="mt-6">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('admin.users.index') }}" class="ml-2 text-lg text-gray-600">Batal</a>
                </div>
            </form>
    </div>
</x-app-layout>
