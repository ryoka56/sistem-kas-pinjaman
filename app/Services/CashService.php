<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Transaction;

class CashService
{
    public function totalIncome(): int
    {
        return (int) Transaction::where('type', TransactionType::Income)->sum('amount');
    }

    public function totalExpense(): int
    {
        return (int) Transaction::where('type', TransactionType::Expense)->sum('amount');
    }

    public function totalLent(): int
    {
        return (int) Loan::sum('amount');
    }

    public function totalRepaid(): int
    {
        return (int) LoanRepayment::sum('amount');
    }

    public function balance(): int
    {
        return $this->totalIncome() + $this->totalRepaid()
            - $this->totalExpense() - $this->totalLent();
    }

    public function totalUnpaidLoans(): int
    {
        return (int) Loan::where('status', LoanStatus::Unpaid)->get()->sum->remaining_amount;
    }

    /** Satu gerbang untuk semua operasi yang mengeluarkan uang dari kas. */
    public function ensureAffordable(int $amount): void
    {
        if ($amount > $this->balance()) {
            throw InsufficientBalanceException::forExpense();
        }
    }
}
