# CBT.EDUKA

CBT.EDUKA adalah platform Computer Based Test untuk institusi, pengajar, dan peserta didik.

Tagline: **Ujian Online yang Jujur, Cepat, dan Terukur.**

## Stack

- Laravel 13.43
- PHP 8.3+
- MySQL/MariaDB
- Blade + Vite
- Tailwind CSS

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env` dan isi kredensial database MySQL.
2. Jalankan `composer install` dan `npm install`.
3. Jalankan `php artisan key:generate`.
4. Jalankan `php artisan migrate --seed` pada local/testing.
5. Jalankan `npm run dev` dan `php artisan serve`.

Untuk production, gunakan worker queue karena publikasi ujian dan notifikasi peserta memakai queue database:

```bash
php artisan queue:work --queue=notifications,default
```

Seeder hanya membuat atau memperbarui akun demo ketika `APP_ENV` adalah `local`
atau `testing` dan `CBT_DEMO_ACCOUNTS_ENABLED=true`. Seeder tidak mereset
database dan tidak mengubah akun/password production.

Akun demo local/testing:

- Admin: `admin@eduka.test` / `EdukaDemo2026!`
- Pengajar: `pengajar@eduka.test` / `EdukaDemo2026!`
- Peserta: `siswa@eduka.test` / `EdukaDemo2026!`

Generator soal AI memakai adapter provider yang terpisah. Groq dapat diaktifkan dengan konfigurasi server di `.env`; ketika tidak aktif atau belum lengkap, UI menampilkan status faktual dan tidak membuat pertanyaan sintetis sebagai fallback. Hasil generation selalu menjadi draft yang harus ditinjau sebelum disimpan ke bank soal.

Lihat [docs/ai-groq-integration.md](docs/ai-groq-integration.md) untuk konfigurasi, smoke test, status provider, validasi delapan tipe soal, dan troubleshooting.

Test suite menggunakan database MySQL `cbt_eduka_testing` melalui `.env.testing`/`phpunit.xml` karena environment ini tidak menyediakan `pdo_sqlite`.

## Verifikasi dasar

```bash
php artisan test
npm run build
```

Dokumen desain dan implementation plan berada di `docs/superpowers/`.
