<?php

namespace App\Exceptions;

use Illuminate\Http\RedirectResponse;
use RuntimeException;

class OverpaymentException extends RuntimeException
{
    public static function make(): self
    {
        return new self('Nominal pembayaran melebihi sisa pinjaman.');
    }

    public function render(): RedirectResponse
    {
        return back()->withInput()->withErrors(['amount' => $this->getMessage()]);
    }
}
