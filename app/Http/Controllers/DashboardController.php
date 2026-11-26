<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Transaction;
use App\Services\CashService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private CashService $cash) {}

    public function index(): View
    {
        $year = now()->year;

        $incomePerMonth = $this->sumPerMonth('income', $year);
        $expensePerMonth = $this->sumPerMonth('expense', $year);

        $recent = collect()
            ->concat(Transaction::latest('transaction_date')->latest('id')->limit(10)->get()->map(fn (Transaction $t) => [
                'sort' => $t->transaction_date->toDateString().' '.$t->created_at->format('H:i:s'),
                'date' => $t->transaction_date->toDateString(),
                'title' => $t->category,
                'type' => $t->type->label(),
                'amount' => $t->type->value === 'income' ? $t->amount : -$t->amount,
            ]))
            ->concat(Loan::with('member')->latest('loan_date')->latest('id')->limit(10)->get()->map(fn (Loan $l) => [
                'sort' => $l->loan_date->toDateString().' '.$l->created_at->format('H:i:s'),
                'date' => $l->loan_date->toDateString(),
                'title' => 'Pinjaman — '.$l->member->name,
                'type' => 'Pinjaman',
                'amount' => -$l->amount,
            ]))
            ->concat(LoanRepayment::with('loan.member')->latest('payment_date')->latest('id')->limit(10)->get()->map(fn (LoanRepayment $r) => [
                'sort' => $r->payment_date->toDateString().' '.$r->created_at->format('H:i:s'),
                'date' => $r->payment_date->toDateString(),
                'title' => 'Cicilan — '.$r->loan->member->name,
                'type' => 'Pengembalian',
                'amount' => $r->amount,
            ]))
            ->sortByDesc('sort')
            ->take(10)
            ->values();

        return view('dashboard', [
            'balance' => $this->cash->balance(),
            'totalIncome' => $this->cash->totalIncome(),
            'totalExpense' => $this->cash->totalExpense(),
            'totalUnpaid' => $this->cash->totalUnpaidLoans(),
            'months' => array_map(fn ($m) => date('M', mktime(0, 0, 0, $m, 1)), range(1, 12)),
            'incomePerMonth' => $incomePerMonth,
            'expensePerMonth' => $expensePerMonth,
            'recent' => $recent,
        ]);
    }

    /** @return array<int, int> 12 bulan, bulan tanpa data = 0 */
    private function sumPerMonth(string $type, int $year): array
    {
        $rows = Transaction::where('type', $type)
            ->whereYear('transaction_date', $year)
            ->get(['transaction_date', 'amount'])
            ->groupBy(fn (Transaction $t) => $t->transaction_date->month)
            ->map(fn ($group) => (int) $group->sum('amount'));

        return array_map(fn ($m) => (int) ($rows[$m] ?? 0), range(1, 12));
    }
}
