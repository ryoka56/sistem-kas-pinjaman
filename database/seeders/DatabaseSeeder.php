<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')],
        );

        // Data demo hanya sekali, supaya aman dijalankan tiap container start.
        if (Transaction::count() > 0) {
            return;
        }

        // Data demo: skenario Andi di PRD (langkah 1-4), supaya dashboard tidak kosong.
        $andi = Member::updateOrCreate(['name' => 'Andi'], ['phone' => '081234567890']);
        Member::updateOrCreate(['name' => 'Budi'], ['phone' => '089876543210']);

        Transaction::create([
            'type' => 'income',
            'category' => 'Tambahan Dana',
            'amount' => 2_000_000,
            'transaction_date' => now(),
            'description' => 'Tambahan dana',
        ]);

        Transaction::create([
            'type' => 'expense',
            'category' => 'Kebutuhan',
            'amount' => 500_000,
            'transaction_date' => now(),
            'description' => 'Pembelian perlengkapan',
        ]);

        $loans = app(LoanService::class);

        $loan = $loans->disburse($andi, [
            'amount' => 1_000_000,
            'loan_date' => now(),
            'due_date' => now()->addMonth(),
        ]);

        $loans->recordRepayment($loan, [
            'amount' => 400_000,
            'payment_date' => now(),
        ]);
    }
}
