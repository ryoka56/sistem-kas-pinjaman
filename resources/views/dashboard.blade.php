<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Saldo Kas', $balance, 'text-slate-800'],
            ['Total Pemasukan', $totalIncome, 'text-emerald-600'],
            ['Total Pengeluaran', $totalExpense, 'text-red-600'],
            ['Pinjaman Belum Kembali', $totalUnpaid, 'text-amber-600'],
        ] as [$label, $value, $color])
            <div class="rounded-lg bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-semibold {{ $color }}">Rp {{ number_format($value, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 rounded-lg bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">Pemasukan vs Pengeluaran {{ now()->year }}</h3>
        <canvas id="cashChart" height="90"></canvas>
    </div>

    <div class="mt-4 rounded-lg bg-white shadow-sm">
        <h3 class="border-b px-5 py-3 text-sm font-semibold text-gray-700">10 Aktivitas Terakhir</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-5 py-2 font-medium">Tanggal</th>
                    <th class="px-5 py-2 font-medium">Jenis</th>
                    <th class="px-5 py-2 font-medium">Keterangan</th>
                    <th class="px-5 py-2 text-right font-medium">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($recent as $item)
                    <tr>
                        <td class="px-5 py-2">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                        <td class="px-5 py-2">{{ $item['type'] }}</td>
                        <td class="px-5 py-2">{{ $item['title'] }}</td>
                        <td class="px-5 py-2 text-right {{ $item['amount'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $item['amount'] < 0 ? '−' : '+' }} Rp {{ number_format(abs($item['amount']), 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-gray-500">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        // Modul Vite dieksekusi setelah DOM siap, jadi window.Chart belum ada saat parse.
        document.addEventListener('DOMContentLoaded', () => {
            new window.Chart(document.getElementById('cashChart'), {
                type: 'bar',
                data: {
                    labels: @json($months),
                    datasets: [
                        { label: 'Pemasukan', data: @json($incomePerMonth), backgroundColor: '#059669' },
                        { label: 'Pengeluaran', data: @json($expensePerMonth), backgroundColor: '#dc2626' },
                    ],
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { position: 'bottom' } },
                },
            });
        });
    </script>
</x-app-layout>
