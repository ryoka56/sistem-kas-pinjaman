<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\Services\CashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function income(int $amount): void
    {
        $this->actingAs($this->admin())
            ->post(route('kas.store', 'income'), [
                'category' => 'Tambahan Dana',
                'amount' => $amount,
                'transaction_date' => now()->toDateString(),
            ])->assertRedirect();
    }

    public function test_saldo_mengikuti_alur_prd(): void
    {
        $cash = app(CashService::class);

        $this->income(2_000_000);
        $this->assertSame(2_000_000, $cash->balance());

        $this->actingAs($this->admin())->post(route('kas.store', 'expense'), [
            'category' => 'Kebutuhan',
            'amount' => 500_000,
            'transaction_date' => now()->toDateString(),
        ])->assertRedirect();
        $this->assertSame(1_500_000, $cash->balance());
    }

    public function test_pengeluaran_ditolak_kalau_saldo_tidak_cukup(): void
    {
        $this->income(100_000);

        $this->actingAs($this->admin())->post(route('kas.store', 'expense'), [
            'category' => 'Kebutuhan',
            'amount' => 200_000,
            'transaction_date' => now()->toDateString(),
        ])->assertSessionHasErrors('amount');

        $this->assertSame(100_000, app(CashService::class)->balance());
    }

    public function test_cicilan_sampai_lunas_mengubah_status(): void
    {
        $this->income(2_000_000);
        $member = Member::create(['name' => 'Andi']);

        $this->actingAs($this->admin())->post(route('loans.store'), [
            'member_id' => $member->id,
            'amount' => 1_000_000,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect();

        $loan = Loan::firstOrFail();
        $this->assertSame(LoanStatus::Unpaid, $loan->status);
        $this->assertSame(1_000_000, app(CashService::class)->balance());

        $this->actingAs($this->admin())->post(route('repayments.store'), [
            'loan_id' => $loan->id,
            'amount' => 400_000,
            'payment_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(600_000, $loan->fresh()->remaining_amount);
        $this->assertSame(LoanStatus::Unpaid, $loan->fresh()->status);

        $this->actingAs($this->admin())->post(route('repayments.store'), [
            'loan_id' => $loan->id,
            'amount' => 600_000,
            'payment_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(0, $loan->fresh()->remaining_amount);
        $this->assertSame(LoanStatus::Paid, $loan->fresh()->status);
        $this->assertSame(2_000_000, app(CashService::class)->balance());
    }

    public function test_cicilan_melebihi_sisa_ditolak(): void
    {
        $this->income(2_000_000);
        $member = Member::create(['name' => 'Andi']);
        $loan = app(\App\Services\LoanService::class)->disburse($member, [
            'amount' => 1_000_000,
            'loan_date' => now(),
        ]);

        $this->actingAs($this->admin())->post(route('repayments.store'), [
            'loan_id' => $loan->id,
            'amount' => 1_200_000,
            'payment_date' => now()->toDateString(),
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, $loan->repayments()->count());
    }

    public function test_hapus_cicilan_mengembalikan_status_belum_lunas(): void
    {
        $this->income(2_000_000);
        $member = Member::create(['name' => 'Andi']);
        $loan = app(\App\Services\LoanService::class)->disburse($member, [
            'amount' => 1_000_000,
            'loan_date' => now(),
        ]);
        $repayment = app(\App\Services\LoanService::class)
            ->recordRepayment($loan, ['amount' => 1_000_000, 'payment_date' => now()]);

        $this->assertSame(LoanStatus::Paid, $loan->fresh()->status);

        $this->actingAs($this->admin())
            ->delete(route('repayments.destroy', $repayment))
            ->assertRedirect();

        $this->assertSame(LoanStatus::Unpaid, $loan->fresh()->status);
        $this->assertSame(1_000_000, $loan->fresh()->remaining_amount);
    }

    public function test_hapus_pinjaman_bercicilan_ditolak(): void
    {
        $this->income(2_000_000);
        $member = Member::create(['name' => 'Andi']);
        $loan = app(\App\Services\LoanService::class)->disburse($member, [
            'amount' => 1_000_000,
            'loan_date' => now(),
        ]);
        app(\App\Services\LoanService::class)
            ->recordRepayment($loan, ['amount' => 100_000, 'payment_date' => now()]);

        $this->actingAs($this->admin())
            ->delete(route('loans.destroy', $loan))
            ->assertSessionHasErrors('general');

        $this->assertDatabaseHas('loans', ['id' => $loan->id]);
    }

    public function test_ubah_nominal_pinjaman_ditolak_saat_sudah_ada_cicilan(): void
    {
        $this->income(2_000_000);
        $member = Member::create(['name' => 'Andi']);
        $loan = app(\App\Services\LoanService::class)->disburse($member, [
            'amount' => 1_000_000,
            'loan_date' => now(),
        ]);
        app(\App\Services\LoanService::class)
            ->recordRepayment($loan, ['amount' => 100_000, 'payment_date' => now()]);

        $this->actingAs($this->admin())->put(route('loans.update', $loan), [
            'member_id' => $member->id,
            'amount' => 900_000,
            'loan_date' => now()->toDateString(),
            'due_date' => null,
        ])->assertSessionHasErrors('general');

        $this->assertSame(1_000_000, $loan->fresh()->amount);
    }

    public function test_hapus_pemasukan_yang_membuat_saldo_minus_ditolak(): void
    {
        $this->income(1_000_000);
        $transaction = \App\Models\Transaction::firstOrFail();

        $this->actingAs($this->admin())->post(route('kas.store', 'expense'), [
            'category' => 'Kebutuhan',
            'amount' => 800_000,
            'transaction_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin())
            ->delete(route('kas.destroy', $transaction->fresh()))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    public function test_registrasi_tertutup(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_halaman_utama_bisa_dibuka_admin(): void
    {
        $this->income(1_000_000);

        $this->actingAs($this->admin())->get('/dashboard')->assertOk()->assertSee('Saldo Kas');
        $this->actingAs($this->admin())->get('/kas')->assertOk();
        $this->actingAs($this->admin())->get('/loans')->assertOk();
        $this->actingAs($this->admin())->get('/repayments')->assertOk();
        $this->actingAs($this->admin())->get('/members')->assertOk();
        $this->actingAs($this->admin())->get('/reports')->assertOk();
    }
}
