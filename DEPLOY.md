# Deploy Gratis: Render + TiDB Cloud

Tujuan: aplikasi bisa dibuka dosen lewat URL, biaya Rp 0, tanpa kartu kredit.

| Bagian | Layanan | Peran |
|---|---|---|
| Aplikasi (PHP) | Render Free (Docker) | Menjalankan Laravel, URL `*.onrender.com` |
| Database | TiDB Cloud Starter | MySQL-compatible, 5 GiB gratis |

Total waktu: ~30 menit, kebanyakan menunggu build.

---

## 1. TiDB Cloud (database)

1. Daftar di https://tidbcloud.com (email atau GitHub, tanpa kartu).
2. **Create Cluster** → pilih **Starter** → mode **MySQL-compatible** → region **Singapore**.
3. Setelah cluster jadi, klik **Connect** → **Public endpoint**. Catat:
   - `HOST` → `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`
   - `PORT` → `4000`
   - `USER` → `<prefix>.root`
   - `PASSWORD` → (generate, salin)
4. Buka **SQL Editor** di console TiDB, jalankan:
   ```sql
   CREATE DATABASE sistem_kas;
   ```
   (Laravel tidak membuat database sendiri.)
5. TLS: tidak perlu unduh sertifikat. Image PHP memakai CA bundle sistem Debian
   (`/etc/ssl/certs/ca-certificates.crt`) yang sudah memuat root CA penerbit sertifikat
   TiDB, dan `render.yaml` sudah menunjuk ke sana. Kalau koneksi tetap ditolak,
   unduh CA dari dialog Connect TiDB, taruh di `docker/tidb-ca.pem`, lalu ubah
   `MYSQL_ATTR_SSL_CA` di `render.yaml` jadi `/var/www/html/docker/tidb-ca.pem`.

## 2. Render (aplikasi)

1. Daftar di https://render.com pakai akun GitHub (tanpa kartu kredit).
2. **New** → **Blueprint** → pilih repository ini → Render membaca `render.yaml`.
3. Isi environment yang ditandai `sync: false`:

   | Key | Isi |
   |---|---|
   | `APP_KEY` | hasil `php artisan key:generate --show` |
   | `APP_URL` | `https://<nama-service>.onrender.com` (boleh diisi setelah deploy pertama) |
   | `DB_HOST` | host TiDB dari langkah 1 |
   | `DB_USERNAME` | `<prefix>.root` |
   | `DB_PASSWORD` | password TiDB |

4. **Apply** → tunggu build selesai (±3–5 menit).
5. Buka URL-nya, login `admin@example.com` / `password`.

Container otomatis menjalankan `php artisan migrate --force --seed` saat start (lihat `docker/entrypoint.sh`), jadi tabel dan data demo langsung ada.

---

## Yang harus kamu tahu soal Render Free

| Hal | Konsekuensi |
|---|---|
| Tidur setelah 15 menit tanpa trafik | Request pertama butuh ±1 menit untuk bangun. Bilang ke dosen: "kunjungan pertama agak lambat, itu normal" |
| 750 jam instance/bulan | Cukup untuk satu service aktif penuh (720 jam) |
| Filesystem hilang saat restart | Tidak masalah — aplikasi ini tidak menyimpan file upload |
| Build gagal di Apache? | Fallback: ganti baris terakhir `Dockerfile` jadi `CMD ["sh","-c","php artisan serve --host=0.0.0.0 --port=${PORT:-80}"]` |

Alternatif kalau butuh **selalu bangun** (tanpa tidur): Oracle Cloud Always Free (VPS ARM gratis permanen), tapi setup manual nginx+php-fpm ~1–2 jam dan verifikasi kartu saat daftar.

## Menghindari "tidur" tanpa bayar

Daftar gratis di https://cron-job.org → buat job yang memanggil `https://<service>.onrender.com/login` **tiap 10 menit**. Trafik masuk menunda spin-down, jadi aplikasi praktis selalu siap saat dosen membukanya. Kuota 750 jam/bulan masih cukup (satu bulan penuh = 720 jam).

## Di mana apa berjalan

| Komponen | Jalan di | Perlu laptopmu? |
|---|---|---|
| Laravel + Apache | Container Render (Singapura) | tidak |
| MySQL (TiDB) | TiDB Cloud (Singapura) | tidak |
| CSS/JS/Chart.js | Dibangun di dalam image saat build | tidak |
| File upload | Tidak ada fitur upload | — |

Laptop hanya dipakai untuk `git push` saat ada perubahan kode. Setelah itu tidak ada yang bergantung pada mesinmu — MySQL lokal di D: hanya untuk kerja harian.

## Perintah lokal yang berguna

```bash
php artisan key:generate --show   # APP_KEY untuk Render
```
