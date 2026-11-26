<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Exceptions\OverpaymentException;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(private CashService $cash) {}

    public function disburse(Member $member, array $data): Loan
    {
        $this->cash->ensureAffordable((int) $data['amount']);

        return $member->loans()->create([
            'amount' => (int) $data['amount'],
            'loan_date' => $data['loan_date'],
            'due_date' => $data['due_date'] ?? null,
            'status' => LoanStatus::Unpaid,
        ]);
    }

    public function remaining(Loan $loan): int
    {
        return $loan->amount - (int) $loan->repayments()->sum('amount');
    }

    public function recordRepayment(Loan $loan, array $data): LoanRepayment
    {
        return DB::transaction(function () use ($loan, $data) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            $amount = (int) $data['amount'];

            if ($amount > $this->remaining($loan)) {
                throw OverpaymentException::make();
            }

            $repayment = $loan->repayments()->create([
                'amount' => $amount,
                'payment_date' => $data['payment_date'],
            ]);

            $this->refreshStatus($loan);

            return $repayment;
        });
    }

    public function deleteRepayment(LoanRepayment $repayment): void
    {
        DB::transaction(function () use ($repayment) {
            $loan = $repayment->loan;
            $repayment->delete();
            $this->refreshStatus($loan);
        });
    }

    /** Satu-satunya tempat yang menulis loans.status. */
    public function refreshStatus(Loan $loan): void
    {
        $loan->update([
            'status' => $this->remaining($loan) <= 0 ? LoanStatus::Paid : LoanStatus::Unpaid,
        ]);
    }
}
