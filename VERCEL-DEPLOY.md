# Deploy Gratis: Vercel + TiDB Cloud (tanpa kartu kredit)

Vercel Hobby tidak meminta kartu. Laravel jalan sebagai **container function** (FrankenPHP),
database di **TiDB Cloud Starter** (MySQL-compatible, gratis).

| Komponen | Jalan di | Perlu laptop? |
|---|---|---|
| Laravel + FrankenPHP | Container function Vercel | tidak |
| Database MySQL | TiDB Cloud (Singapore) | tidak |
| Aset CSS/JS | Dibangun saat image di-build | tidak |

File khusus Vercel: `Dockerfile.vercel`, `Caddyfile`, `vercel.json`.
(`Dockerfile` + `render.yaml` dibiarkan sebagai jalur cadangan.)

---

## 1. TiDB Cloud

1. Daftar di https://tidbcloud.com (tanpa kartu).
2. **Create Cluster** → **Starter** → **MySQL-compatible** → region **Singapore**.
3. Buka **SQL Editor**, jalankan:
   ```sql
   CREATE DATABASE sistem_kas;
   ```
4. **Connect** → **Public endpoint**. Catat: host, port `4000`, user `<prefix>.root`, password.
5. Unduh **CA cert** dari dialog yang sama. Simpan di luar repo, misalnya:
   ```
   D:\Project PBO web\tools\tidb-ca.pem
   ```
   (Dipakai hanya saat migrasi dari laptop; di Vercel pakai CA bawaan Debian.)

## 2. Migrasi + data demo (sekali, dari laptop)

Container **tidak** menjalankan migrasi saat start — jalankan sekali dari laptop:

```bash
cd "D:/Project PBO web/sistem-kas"
DB_CONNECTION=mysql \
DB_HOST=<host-tidb> DB_PORT=4000 DB_DATABASE=sistem_kas \
DB_USERNAME=<prefix>.root DB_PASSWORD=<password> \
MYSQL_ATTR_SSL_CA="D:/Project PBO web/tools/tidb-ca.pem" \
php artisan migrate --seed --force
```

Kalau berhasil, tabel + data demo (skenario Andi) langsung ada di TiDB.

## 3. Vercel

1. Daftar/login https://vercel.com pakai GitHub (Hobby, tanpa kartu).
2. **Add New → Project → Import** repo `sistem-kas-pinjaman`.
   Framework Preset: biarkan; Vercel membaca `vercel.json` (`runtime: container`).
3. **Environment Variables** (Production):

   | Key | Nilai |
   |---|---|
   | `APP_KEY` | hasil `php artisan key:generate --show` |
   | `APP_URL` | `https://<project>.vercel.app` |
   | `DB_CONNECTION` | `mysql` |
   | `DB_HOST` | host TiDB |
   | `DB_PORT` | `4000` |
   | `DB_DATABASE` | `sistem_kas` |
   | `DB_USERNAME` | `<prefix>.root` |
   | `DB_PASSWORD` | password TiDB |
   | `MYSQL_ATTR_SSL_CA` | `/etc/ssl/certs/ca-certificates.crt` |

4. **Deploy**. Build pertama meng-compile image (node + composer + FrankenPHP), ±5–8 menit.
5. Buka URL → login `admin@example.com` / `password`.

## Yang perlu diketahui

- **Tidak ada spin-down** seperti Render. Instance dibuat saat ada trafik, tapi cold start
  hitungan detik, bukan satu menit.
- **Filesystem tidak permanen.** Karena itu `SESSION_DRIVER` dan `CACHE_STORE` diset `database`,
  log ke `stderr`, dan Blade sudah di-`view:cache` saat build. Tidak ada fitur upload file.
- `config:cache` **tidak** dipakai — env baru tersedia saat container start, bukan saat build.
  Ini sesuai panduan resmi Vercel.
- Kalau deploy menolak `runtime: container` di plan Hobby, jalur cadangan: Render Free
  (`render.yaml` + `Dockerfile`), atau shared hosting PHP gratis.
