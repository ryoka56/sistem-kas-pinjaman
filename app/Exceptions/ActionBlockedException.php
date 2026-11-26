<?php

namespace App\Exceptions;

use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ActionBlockedException extends RuntimeException
{
    public static function loanHasRepayments(): self
    {
        return new self('Pinjaman tidak dapat dihapus karena sudah memiliki riwayat pengembalian.');
    }

    public static function memberHasLoans(): self
    {
        return new self('Anggota masih memiliki pinjaman.');
    }

    public static function loanAmountLocked(): self
    {
        return new self('Nominal pinjaman tidak dapat diubah karena sudah ada cicilan.');
    }

    public function render(): RedirectResponse
    {
        return back()->withInput()->withErrors(['general' => $this->getMessage()]);
    }
}
