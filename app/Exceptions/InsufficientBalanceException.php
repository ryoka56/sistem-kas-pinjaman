<?php

namespace App\Exceptions;

use Illuminate\Http\RedirectResponse;
use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public static function forExpense(): self
    {
        return new self('Saldo kas tidak mencukupi.');
    }

    public static function forDeletion(): self
    {
        return new self('Tidak bisa menghapus: saldo kas akan minus.');
    }

    /** Exception yang tahu cara menampilkan dirinya — controller jadi tanpa try/catch. */
    public function render(): RedirectResponse
    {
        return back()->withInput()->withErrors(['amount' => $this->getMessage()]);
    }
}
