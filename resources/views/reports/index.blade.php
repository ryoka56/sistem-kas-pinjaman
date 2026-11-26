<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800">Laporan Keuangan</h2>
            <a href="{{ route('reports.pdf', ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()]) }}"
               class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900">Export PDF</a>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('reports.index') }}" class="rounded-lg bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <x-input-label for="start_date" value="Dari Tanggal" />
                <x-text-input id="start_date" name="start_date" type="date" class="mt-1"
                              :value="$start->toDateString()" />
            </div>
            <div>
                <x-input-label for="end_date" value="Sampai Tanggal" />
                <x-text-input id="end_date" name="end_date" type="date" class="mt-1"
                              :value="$end->toDateString()" />
            </div>
            <x-primary-button>Tampilkan</x-primary-button>
        </div>
    </form>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['Pemasukan (periode)', $income, 'text-emerald-600'],
            ['Pengeluaran (periode)', $expense, 'text-red-600'],
            ['Pinjaman Diberikan (periode)', $lent, 'text-amber-600'],
            ['Pengembalian Diterima (periode)', $received, 'text-emerald-600'],
            ['Sisa Pinjaman Belum Lunas (keseluruhan)', $unpaidTotal, 'text-amber-600'],
            ['Saldo Kas Saat Ini (keseluruhan)', $balance, 'text-slate-800'],
        ] as [$label, $value, $color])
            <div class="rounded-lg bg-white p-4 shadow-sm">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-xl font-semibold {{ $color }}">Rp {{ number_format($value, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 rounded-lg bg-white shadow-sm">
        <h3 class="border-b px-5 py-3 text-sm font-semibold text-gray-700">Transaksi Kas</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Tanggal</th>
                    <th class="px-4 py-2 font-medium">Jenis</th>
                    <th class="px-4 py-2 font-medium">Kategori</th>
                    <th class="px-4 py-2 text-right font-medium">Nominal</th>
                    <th class="px-4 py-2 font-medium">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-2">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $transaction->type->label() }}</td>
                        <td class="px-4 py-2">{{ $transaction->category }}</td>
                        <td class="px-4 py-2 text-right {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-600' : 'text-red-600' }}">
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $transaction->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 rounded-lg bg-white shadow-sm">
        <h3 class="border-b px-5 py-3 text-sm font-semibold text-gray-700">Pinjaman Diberikan</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Tanggal</th>
                    <th class="px-4 py-2 font-medium">Anggota</th>
                    <th class="px-4 py-2 text-right font-medium">Pokok</th>
                    <th class="px-4 py-2 text-right font-medium">Terbayar</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($loans as $loan)
                    <tr>
                        <td class="px-4 py-2">{{ $loan->loan_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $loan->member->name }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($loan->total_repaid, 0, ',', '.') }}</td>
                        <td class="px-4 py-2">{{ $loan->status->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada pinjaman pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 rounded-lg bg-white shadow-sm">
        <h3 class="border-b px-5 py-3 text-sm font-semibold text-gray-700">Pengembalian Pinjaman</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Tanggal</th>
                    <th class="px-4 py-2 font-medium">Anggota</th>
                    <th class="px-4 py-2 text-right font-medium">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($repayments as $repayment)
                    <tr>
                        <td class="px-4 py-2">{{ $repayment->payment_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $repayment->loan->member->name }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($repayment->amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Tidak ada pengembalian pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
