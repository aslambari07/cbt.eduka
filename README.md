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
4. Jalankan `php artisan migrate --seed`.
5. Jalankan `npm run dev` dan `php artisan serve`.

Test suite menggunakan database MySQL `cbt_eduka_testing` melalui `.env.testing`/`phpunit.xml` karena environment ini tidak menyediakan `pdo_sqlite`.

## Verifikasi dasar

```bash
php artisan test
npm run build
```

Dokumen desain dan implementation plan berada di `docs/superpowers/`.
