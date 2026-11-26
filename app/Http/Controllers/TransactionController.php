<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Services\CashService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(private CashService $cash) {}

    public function index(Request $request): View
    {
        $type = TransactionType::tryFrom((string) $request->query('type'));

        $transactions = Transaction::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('kas.index', compact('transactions', 'type'));
    }

    public function create(string $type): View
    {
        return view('kas.create', ['type' => TransactionType::from($type)]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $type = TransactionType::from($type);
        $data = $this->validated($request);

        if ($type === TransactionType::Expense) {
            $this->cash->ensureAffordable($data['amount']);
        }

        Transaction::create($data + ['type' => $type]);

        return redirect()->route('kas.index', ['type' => $type->value])
            ->with('status', $type->label().' berhasil disimpan.');
    }

    public function edit(Transaction $kas): View
    {
        return view('kas.edit', ['transaction' => $kas]);
    }

    public function update(Request $request, Transaction $kas): RedirectResponse
    {
        $data = $this->validated($request);

        // Saldo boleh dipakai sebesar nilai lama + saldo sekarang, untuk kedua tipe.
        if ($data['amount'] > $this->cash->balance() + $kas->amount) {
            throw InsufficientBalanceException::forExpense();
        }

        $kas->update($data);

        return redirect()->route('kas.index')->with('status', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $kas): RedirectResponse
    {
        if ($kas->type === TransactionType::Income && $kas->amount > $this->cash->balance()) {
            throw InsufficientBalanceException::forDeletion();
        }

        $kas->delete();

        return redirect()->route('kas.index')->with('status', 'Transaksi berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'string', 'max:100', Rule::in(Transaction::CATEGORIES)],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ], [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.integer' => 'Nominal harus berupa angka bulat.',
            'amount.min' => 'Nominal minimal Rp 1.',
            'transaction_date.required' => 'Tanggal wajib diisi.',
            'transaction_date.date' => 'Tanggal tidak valid.',
        ]);
    }
}
