<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Ubah Pinjaman</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('loans.update', $loan) }}">
            @csrf
            @method('PUT')
            @include('loans._form')
        </form>
    </div>
</x-app-layout>
