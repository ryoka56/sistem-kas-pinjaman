<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Tambah Anggota</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('members.store') }}">
            @csrf
            @include('members._form', ['member' => null])
        </form>
    </div>
</x-app-layout>
