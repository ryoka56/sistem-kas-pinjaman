<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Tambah {{ $type->label() }}</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('kas.store', $type->value) }}">
            @csrf
            @include('kas._form', ['transaction' => null])
        </form>
    </div>
</x-app-layout>
