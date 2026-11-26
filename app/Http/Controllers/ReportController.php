<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Enums\TransactionType;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Transaction;
use App\Services\CashService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private CashService $cash) {}

    public function index(Request $request): View
    {
        return view('reports.index', $this->data($request));
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->data($request);

        return Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('laporan-'.$data['start']->toDateString().'-'.$data['end']->toDateString().'.pdf');
    }

    private function data(Request $request): array
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ], [
            'start_date.date' => 'Tanggal mulai tidak valid.',
            'end_date.date' => 'Tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus setelah tanggal mulai.',
        ]);

        $start = $request->filled('start_date') ? Carbon::parse($request->query('start_date')) : now()->startOfMonth();
        $end = $request->filled('end_date') ? Carbon::parse($request->query('end_date')) : now()->endOfMonth();
        $range = [$start->toDateString(), $end->toDateString()];

        $transactions = Transaction::whereBetween('transaction_date', $range)
            ->orderBy('transaction_date')->orderBy('id')->get();

        $loans = Loan::with('member')->withSum('repayments', 'amount')
            ->whereBetween('loan_date', $range)->orderBy('loan_date')->get();

        $repayments = LoanRepayment::with('loan.member')
            ->whereBetween('payment_date', $range)->orderBy('payment_date')->get();

        return [
            'start' => $start,
            'end' => $end,
            'income' => (int) $transactions->where('type', TransactionType::Income)->sum('amount'),
            'expense' => (int) $transactions->where('type', TransactionType::Expense)->sum('amount'),
            'lent' => (int) $loans->sum('amount'),
            'received' => (int) $repayments->sum('amount'),
            'unpaidTotal' => $this->cash->totalUnpaidLoans(),
            'balance' => $this->cash->balance(),
            'transactions' => $transactions,
            'loans' => $loans,
            'repayments' => $repayments,
        ];
    }
}
