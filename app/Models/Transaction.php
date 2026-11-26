<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['type', 'category', 'amount', 'transaction_date', 'description'];

    protected $casts = [
        'type' => TransactionType::class,
        'amount' => 'integer',
        'transaction_date' => 'date',
    ];

    public const CATEGORIES = [
        'Tambahan Dana', 'Iuran', 'Kebutuhan', 'Perlengkapan', 'Lainnya',
    ];
}
