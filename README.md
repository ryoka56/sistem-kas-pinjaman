# Sistem Kas & Peminjaman — Panduan Jalan

Laravel 12 + MySQL, single admin. Spesifikasi lengkap: `../PRD-v2.md`.

## Akun

| Email | Password |
|---|---|
| `admin@example.com` | `password` |

Tidak ada halaman registrasi (memang ditutup).

## Cara menjalankan

1. **MySQL** (dari D:, bukan service):
   ```
   "D:\Project PBO web\tools\mysql\bin\mysqld.exe" --defaults-file="D:\Project PBO web\tools\mysql\my.ini" --console
   ```
   Biarkan jendela itu terbuka.
2. **Aplikasi**, di folder ini:
   ```
   php artisan serve
   ```
3. Buka http://127.0.0.1:8000

Atau klik `mulai.bat` (melakukan langkah 1 dan 2 sekaligus).

## Perintah yang sering dipakai

| Perintah | Fungsi |
|---|---|
| `php artisan test` | 32 tes: saldo, penolakan saldo kurang, cicilan, status lunas |
| `php artisan migrate:fresh --seed` | Reset database + data demo (skenario Andi) |
| `npm run build` | Build ulang CSS/JS kalau ada perubahan tampilan |

## Peta kode (untuk sidang)

| Lokasi | Isi |
|---|---|
| `app/Enums/` | `TransactionType`, `LoanStatus` — pengganti string `'income'`/`'unpaid'` |
| `app/Services/CashService.php` | `balance()`, `totalUnpaidLoans()`, `ensureAffordable()` |
| `app/Services/LoanService.php` | `disburse()`, `recordRepayment()`, `deleteRepayment()`, `refreshStatus()` |
| `app/Exceptions/` | `InsufficientBalanceException`, `OverpaymentException`, `ActionBlockedException` — masing-masing punya `render()` |
| `app/Http/Controllers/` | 6 controller tipis: Dashboard, Transaction (Kas), Member, Loan, Repayment, Report |
| `tests/Feature/LoanFlowTest.php` | 10 tes aturan bisnis (R1–R5 di PRD) |

Aturan yang paling mungkin ditanya dosen:

- Saldo **tidak disimpan** di kolom; dihitung di `CashService::balance()`.
- `loans.status` hanya ditulis oleh `LoanService::refreshStatus()`.
- Semua penolakan uang melempar exception yang sudah tahu cara redirect balik dengan pesan.
