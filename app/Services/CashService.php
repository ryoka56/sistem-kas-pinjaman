<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use Illuminate\Support\Facades\DB;

class CashService
{
    /**
     * Semua angka kas dalam SATU query, selalu segar (tidak di-cache).
     * Database bisa jauh, jadi jumlah round-trip harus sedikit.
     *
     * @return array{income:int, expense:int, lent:int, repaid:int, unpaid:int, balance:int}
     */
    public function summary(): array
    {
        $row = DB::selectOne("
            SELECT
                (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'income')  AS income,
                (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'expense') AS expense,
                (SELECT COALESCE(SUM(amount), 0) FROM loans)                               AS lent,
                (SELECT COALESCE(SUM(amount), 0) FROM loan_repayments)                     AS repaid,
                (SELECT COALESCE(SUM(l.amount - COALESCE(r.repaid, 0)), 0)
                   FROM loans l
                   LEFT JOIN (SELECT loan_id, SUM(amount) AS repaid FROM loan_repayments GROUP BY loan_id) r
                     ON r.loan_id = l.id
                  WHERE l.status = 'unpaid')                                              AS unpaid
        ");

        $income = (int) $row->income;
        $expense = (int) $row->expense;
        $lent = (int) $row->lent;
        $repaid = (int) $row->repaid;
        $unpaid = (int) $row->unpaid;

        return [
            'income' => $income,
            'expense' => $expense,
            'lent' => $lent,
            'repaid' => $repaid,
            'unpaid' => $unpaid,
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
        return $this->summary()['unpaid'];
    }

    /** Satu gerbang untuk semua operasi yang mengeluarkan uang dari kas. */
    public function ensureAffordable(int $amount): void
    {
        if ($amount > $this->balance()) {
            throw InsufficientBalanceException::forExpense();
        }
    }
}
