<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;

class CashService
{
    /**
     * Empat angka dasar kas dalam SATU query, selalu segar (tidak di-cache).
     * Database bisa jauh (TiDB ~120 ms/query), jadi jumlah query harus sedikit.
     *
     * @return array{income:int, expense:int, lent:int, repaid:int, balance:int}
     */
    public function summary(): array
    {
        $row = DB::selectOne("
            SELECT
                (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'income')  AS income,
                (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'expense') AS expense,
                (SELECT COALESCE(SUM(amount), 0) FROM loans)                               AS lent,
                (SELECT COALESCE(SUM(amount), 0) FROM loan_repayments)                     AS repaid
        ");

        $income = (int) $row->income;
        $expense = (int) $row->expense;
        $lent = (int) $row->lent;
        $repaid = (int) $row->repaid;

        return [
            'income' => $income,
            'expense' => $expense,
            'lent' => $lent,
            'repaid' => $repaid,
            'balance' => $income + $repaid - $expense - $lent,
        ];
    }

    /** Rumus saldo: (pemasukan + cicilan) − (pengeluaran + pinjaman). */
    public function balance(): int
    {
        return $this->summary()['balance'];
    }

    public function totalUnpaidLoans(): int
    {
        return (int) Loan::where('status', LoanStatus::Unpaid)
            ->withSum('repayments', 'amount')
            ->get()
            ->sum->remaining_amount;
    }

    /** Satu gerbang untuk semua operasi yang mengeluarkan uang dari kas. */
    public function ensureAffordable(int $amount): void
    {
        if ($amount > $this->balance()) {
            throw InsufficientBalanceException::forExpense();
        }
    }
}
