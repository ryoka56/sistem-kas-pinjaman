<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/kas', [TransactionController::class, 'index'])->name('kas.index');
    Route::get('/kas/{type}/create', [TransactionController::class, 'create'])
        ->whereIn('type', ['income', 'expense'])->name('kas.create');
    Route::post('/kas/{type}', [TransactionController::class, 'store'])
        ->whereIn('type', ['income', 'expense'])->name('kas.store');
    Route::get('/kas/{kas}/edit', [TransactionController::class, 'edit'])->name('kas.edit');
    Route::put('/kas/{kas}', [TransactionController::class, 'update'])->name('kas.update');
    Route::delete('/kas/{kas}', [TransactionController::class, 'destroy'])->name('kas.destroy');

    Route::resource('members', MemberController::class)->except('show');
    Route::resource('loans', LoanController::class)->except('show');

    Route::get('/repayments', [RepaymentController::class, 'index'])->name('repayments.index');
    Route::get('/repayments/create', [RepaymentController::class, 'create'])->name('repayments.create');
    Route::post('/repayments', [RepaymentController::class, 'store'])->name('repayments.store');
    Route::delete('/repayments/{repayment}', [RepaymentController::class, 'destroy'])->name('repayments.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
