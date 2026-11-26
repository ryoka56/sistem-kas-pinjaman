<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800">Kas</h2>
            <div class="flex gap-2">
                <a href="{{ route('kas.create', 'income') }}"
                   class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">+ Pemasukan</a>
                <a href="{{ route('kas.create', 'expense') }}"
                   class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">+ Pengeluaran</a>
            </div>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="flex gap-1 border-b px-3 py-2 text-sm">
            <a href="{{ route('kas.index') }}"
               class="rounded-md px-3 py-1 {{ $type === null ? 'bg-gray-100 font-medium text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">Semua</a>
            @foreach (\App\Enums\TransactionType::cases() as $case)
                <a href="{{ route('kas.index', ['type' => $case->value]) }}"
                   class="rounded-md px-3 py-1 {{ $type === $case ? 'bg-gray-100 font-medium text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">{{ $case->label() }}</a>
            @endforeach
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Tanggal</th>
                    <th class="px-4 py-2 font-medium">Jenis</th>
                    <th class="px-4 py-2 font-medium">Kategori</th>
                    <th class="px-4 py-2 text-right font-medium">Nominal</th>
                    <th class="px-4 py-2 font-medium">Keterangan</th>
                    <th class="px-4 py-2 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-2">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $transaction->type->label() }}</td>
                        <td class="px-4 py-2">{{ $transaction->category }}</td>
                        <td class="px-4 py-2 text-right {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $transaction->type === \App\Enums\TransactionType::Income ? '+' : '−' }}
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $transaction->description }}</td>
                        <td class="whitespace-nowrap px-4 py-2 text-right">
                            <a href="{{ route('kas.edit', $transaction) }}" class="text-indigo-600 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('kas.destroy', $transaction) }}" class="inline"
                                  onsubmit="return confirm('Hapus transaksi ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-2 text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-app-layout>
