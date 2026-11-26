<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Transaction;
use App\Services\CashService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Label bulan untuk grafik. */
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function __construct(private CashService $cash) {}

    public function index(): View
    {
        $year = now()->year;

        return view('dashboard', [
            'balance' => $this->cash->balance(),
            'totalIncome' => $this->cash->totalIncome(),
            'totalExpense' => $this->cash->totalExpense(),
            'totalUnpaid' => $this->cash->totalUnpaidLoans(),
            'months' => self::MONTHS,
            'incomePerMonth' => $this->sumPerMonth(TransactionType::Income, $year),
            'expensePerMonth' => $this->sumPerMonth(TransactionType::Expense, $year),
            'recent' => $this->recent(),
        ]);
    }

    /** Total per bulan: 12 angka, bulan tanpa transaksi diisi 0. */
    private function sumPerMonth(TransactionType $type, int $year): array
    {
        $totals = array_fill(1, 12, 0);

        $transactions = Transaction::where('type', $type)
            ->whereYear('transaction_date', $year)
            ->get(['transaction_date', 'amount']);

        foreach ($transactions as $transaction) {
            $totals[$transaction->transaction_date->month] += $transaction->amount;
        }

        return array_values($totals);
    }

    /** 10 aktivitas terakhir dari 3 sumber, diurutkan dari waktu terbaru. */
    private function recent(): array
    {
        $rows = [];

        foreach (Transaction::latest('transaction_date')->latest('id')->limit(10)->get() as $transaction) {
            $rows[] = [
                'time' => $transaction->transaction_date->toDateString().' '.$transaction->created_at->format('H:i:s'),
                'date' => $transaction->transaction_date,
                'type' => $transaction->type->label(),
                'title' => $transaction->category,
                'amount' => $transaction->type === TransactionType::Income ? $transaction->amount : -$transaction->amount,
            ];
        }

        foreach (Loan::with('member')->latest('loan_date')->latest('id')->limit(10)->get() as $loan) {
            $rows[] = [
                'time' => $loan->loan_date->toDateString().' '.$loan->created_at->format('H:i:s'),
                'date' => $loan->loan_date,
                'type' => 'Pinjaman',
                'title' => 'Pinjaman — '.$loan->member->name,
                'amount' => -$loan->amount,
            ];
        }

        foreach (LoanRepayment::with('loan.member')->latest('payment_date')->latest('id')->limit(10)->get() as $repayment) {
            $rows[] = [
                'time' => $repayment->payment_date->toDateString().' '.$repayment->created_at->format('H:i:s'),
                'date' => $repayment->payment_date,
                'type' => 'Pengembalian',
                'title' => 'Cicilan — '.$repayment->loan->member->name,
                'amount' => $repayment->amount,
            ];
        }

        usort($rows, fn (array $a, array $b) => strcmp($b['time'], $a['time']));

        return array_slice($rows, 0, 10);
    }
}
