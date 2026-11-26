<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = ['member_id', 'amount', 'loan_date', 'due_date', 'status'];

    protected $casts = [
        'amount' => 'integer',
        'loan_date' => 'date',
        'due_date' => 'date',
        'status' => LoanStatus::class,
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * Kalau query memakai withSum('repayments', 'amount'), hasilnya sudah ikut
     * terbaca dari attribute *_sum sehingga tidak ada query per baris.
     */
    public function getTotalRepaidAttribute(): int
    {
        return (int) ($this->repayments_sum_amount ?? $this->repayments()->sum('amount'));
    }

    public function getRemainingAmountAttribute(): int
    {
        return $this->amount - $this->total_repaid;
    }

    public function isOverdue(): bool
    {
        return $this->status === LoanStatus::Unpaid
            && $this->due_date !== null
            && $this->due_date->isPast();
    }
}
