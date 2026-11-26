<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepaymentController extends Controller
{
    public function __construct(private LoanService $loans) {}

    public function index(): View
    {
        $repayments = LoanRepayment::with('loan.member')
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15);

        return view('repayments.index', compact('repayments'));
    }

    public function create(Request $request): View
    {
        $loans = Loan::with('member')
            ->withSum('repayments', 'amount')
            ->where('status', LoanStatus::Unpaid)
            ->get()
            ->sortBy(fn (Loan $loan) => $loan->member->name)
            ->values();

        return view('repayments.create', [
            'loans' => $loans,
            'selected' => (int) $request->query('loan_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'integer', 'exists:loans,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
        ], [
            'loan_id.required' => 'Pinjaman wajib dipilih.',
            'loan_id.exists' => 'Pinjaman tidak ditemukan.',
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.integer' => 'Nominal harus berupa angka bulat.',
            'amount.min' => 'Nominal minimal Rp 1.',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'payment_date.date' => 'Tanggal pembayaran tidak valid.',
        ]);

        $loan = Loan::where('status', LoanStatus::Unpaid)->findOrFail($data['loan_id']);

        $this->loans->recordRepayment($loan, $data);

        return redirect()->route('loans.index')->with('status', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(LoanRepayment $repayment): RedirectResponse
    {
        $this->loans->deleteRepayment($repayment);

        return redirect()->route('repayments.index')
            ->with('status', 'Pembayaran dihapus, status pinjaman diperbarui.');
    }
}
