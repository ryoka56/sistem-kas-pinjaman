<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Peminjaman</h2>
            <a href="{{ route('loans.create') }}"
               class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900">+ Pinjaman</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Anggota</th>
                    <th class="px-4 py-2 text-right font-medium">Pokok</th>
                    <th class="px-4 py-2 text-right font-medium">Terbayar</th>
                    <th class="px-4 py-2 text-right font-medium">Sisa</th>
                    <th class="px-4 py-2 font-medium">Jatuh Tempo</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($loans as $loan)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $loan->member->name }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right text-emerald-600">Rp {{ number_format($loan->total_repaid, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right font-medium {{ $loan->remaining_amount > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                            Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $loan->due_date?->format('d/m/Y') ?? '—' }}
                            @if ($loan->isOverdue())
                                <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-xs text-red-700">Lewat</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $loan->status->badgeClass() }}">
                                {{ $loan->status->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-right">
                            @if ($loan->status === \App\Enums\LoanStatus::Unpaid)
                                <a href="{{ route('repayments.create', ['loan_id' => $loan->id]) }}"
                                   class="rounded bg-emerald-600 px-2 py-1 text-xs font-medium text-white hover:bg-emerald-700">Bayar</a>
                            @endif
                            <a href="{{ route('loans.edit', $loan) }}" class="ml-2 text-indigo-600 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('loans.destroy', $loan) }}" class="inline"
                                  onsubmit="return confirm('Hapus pinjaman ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-2 text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada pinjaman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $loans->links() }}</div>
</x-app-layout>
