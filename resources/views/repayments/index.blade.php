<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Pengembalian Pinjaman</h2>
            <a href="{{ route('repayments.create') }}"
               class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">+ Pembayaran</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Tanggal</th>
                    <th class="px-4 py-2 font-medium">Anggota</th>
                    <th class="px-4 py-2 text-right font-medium">Nominal</th>
                    <th class="px-4 py-2 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($repayments as $repayment)
                    <tr>
                        <td class="px-4 py-2">{{ $repayment->payment_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $repayment->loan->member->name }}</td>
                        <td class="px-4 py-2 text-right text-emerald-600">Rp {{ number_format($repayment->amount, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-4 py-2 text-right">
                            <form method="POST" action="{{ route('repayments.destroy', $repayment) }}" class="inline"
                                  onsubmit="return confirm('Hapus pembayaran ini? Status pinjaman akan dihitung ulang.')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $repayments->links() }}</div>
</x-app-layout>
