<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Tambah Pinjaman</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        @if ($members->isEmpty())
            <p class="text-sm text-gray-600">
                Belum ada anggota. <a href="{{ route('members.create') }}" class="text-indigo-600 hover:underline">Tambah anggota dulu</a>.
            </p>
        @else
            <form method="POST" action="{{ route('loans.store') }}">
                @csrf
                @include('loans._form', ['loan' => new \App\Models\Loan()])
            </form>
        @endif
    </div>
</x-app-layout>
