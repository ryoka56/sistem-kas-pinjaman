<?php

namespace App\Http\Controllers;

use App\Exceptions\ActionBlockedException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Loan;
use App\Models\Member;
use App\Services\CashService;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(
        private LoanService $loans,
        private CashService $cash,
    ) {}

    public function index(): View
    {
        $loans = Loan::with('member')
            ->withSum('repayments', 'amount')
            ->withCount('repayments')
            ->latest('loan_date')
            ->latest('id')
            ->paginate(15);

        return view('loans.index', compact('loans'));
    }

    public function create(): View
    {
        return view('loans.create', [
            'loan' => new Loan(),
            'members' => Member::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $this->loans->disburse(Member::findOrFail($data['member_id']), $data);

        return redirect()->route('loans.index')->with('status', 'Pinjaman berhasil dicatat.');
    }

    public function edit(Loan $loan): View
    {
        $loan->loadSum('repayments', 'amount')->loadCount('repayments');

        return view('loans.edit', [
            'loan' => $loan,
            'members' => Member::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $data = $this->validated($request);
        $locked = $loan->repayments()->exists();

        if ($locked && $data['amount'] !== $loan->amount) {
            throw ActionBlockedException::loanAmountLocked();
        }

        // Saldo boleh dipakai sebesar nilai lama + saldo sekarang.
        if (! $locked && $data['amount'] > $this->cash->balance() + $loan->amount) {
            throw InsufficientBalanceException::forExpense();
        }

        $loan->update($locked
            ? ['member_id' => $data['member_id'], 'due_date' => $data['due_date']]
            : $data);

        if ($locked) {
            $this->loans->refreshStatus($loan);
        }

        return redirect()->route('loans.index')->with('status', 'Pinjaman berhasil diperbarui.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        if ($loan->repayments()->exists()) {
            throw ActionBlockedException::loanHasRepayments();
        }

        $loan->delete();

        return redirect()->route('loans.index')->with('status', 'Pinjaman berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:loan_date'],
        ], [
            'member_id.required' => 'Anggota wajib dipilih.',
            'member_id.exists' => 'Anggota tidak ditemukan.',
            'amount.required' => 'Nominal pinjaman wajib diisi.',
            'amount.integer' => 'Nominal harus berupa angka bulat.',
            'amount.min' => 'Nominal minimal Rp 1.',
            'loan_date.required' => 'Tanggal pinjaman wajib diisi.',
            'loan_date.date' => 'Tanggal pinjaman tidak valid.',
            'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
            'due_date.after_or_equal' => 'Jatuh tempo tidak boleh sebelum tanggal pinjaman.',
        ]);

        $data['amount'] = (int) $data['amount'];

        return $data;
    }
}
