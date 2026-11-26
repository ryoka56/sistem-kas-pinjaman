<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 18px 0 6px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; }
        th { background: #f2f2f2; text-align: left; }
        .right { text-align: right; }
        .summary td { border: none; padding: 2px 0; }
        .summary .value { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan</h1>
    <p class="muted">Periode {{ $start->format('d/m/Y') }} — {{ $end->format('d/m/Y') }}</p>

    <h2>Ringkasan</h2>
    <table class="summary">
        <tr><td>Pemasukan (periode)</td><td class="value">Rp {{ number_format($income, 0, ',', '.') }}</td></tr>
        <tr><td>Pengeluaran (periode)</td><td class="value">Rp {{ number_format($expense, 0, ',', '.') }}</td></tr>
        <tr><td>Pinjaman diberikan (periode)</td><td class="value">Rp {{ number_format($lent, 0, ',', '.') }}</td></tr>
        <tr><td>Pengembalian diterima (periode)</td><td class="value">Rp {{ number_format($received, 0, ',', '.') }}</td></tr>
        <tr><td>Sisa pinjaman belum lunas (keseluruhan)</td><td class="value">Rp {{ number_format($unpaidTotal, 0, ',', '.') }}</td></tr>
        <tr><td>Saldo kas saat ini (keseluruhan)</td><td class="value">Rp {{ number_format($balance, 0, ',', '.') }}</td></tr>
    </table>

    <h2>Transaksi Kas</h2>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th class="right">Nominal</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $transaction->type->label() }}</td>
                    <td>{{ $transaction->category }}</td>
                    <td class="right">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                    <td>{{ $transaction->description }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Pinjaman Diberikan</h2>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Anggota</th><th class="right">Pokok</th><th class="right">Terbayar</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                    <td>{{ $loan->member->name }}</td>
                    <td class="right">Rp {{ number_format($loan->amount, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($loan->total_repaid, 0, ',', '.') }}</td>
                    <td>{{ $loan->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Tidak ada pinjaman pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Pengembalian Pinjaman</h2>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Anggota</th><th class="right">Nominal</th></tr>
        </thead>
        <tbody>
            @forelse ($repayments as $repayment)
                <tr>
                    <td>{{ $repayment->payment_date->format('d/m/Y') }}</td>
                    <td>{{ $repayment->loan->member->name }}</td>
                    <td class="right">Rp {{ number_format($repayment->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">Tidak ada pengembalian pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
