# ICSMI

Situs prosiding International Conference on Sustainable Management and Innovation: halaman publik, arsip prosiding, pengiriman naskah, double-blind review, penerbitan, panel admin, dan laporan.

Dibangun dengan Laravel 13, SQLite, Blade, dan Tailwind CSS. Halaman tidak memuat JavaScript. Ringkasan keamanan ada di [SECURITY-AUDIT.md](SECURITY-AUDIT.md).

## Peran

| Peran | Tugas |
|---|---|
| Admin | Peran pengguna, menu dan beranda, dewan editor dan pembicara, ekspor dan impor konten, audit log, laporan, dan semua fitur editor |
| Editor | Memeriksa naskah, mengundang reviewer, memberi keputusan, menerbitkan, mengelola halaman, pengumuman, dan Media Library, laporan |
| Reviewer | Menerima atau menolak undangan, mengunduh naskah anonim, mengirim review |
| Author | Mengirim naskah, memantau status, mengunggah revisi |

Pendaftar baru selalu menjadi Author. Admin memberikan peran lain lewat **Admin > Users**.

## Submission tanpa login

Halaman `/submission` menerima judul, nama, email, telepon, alamat institusi, abstrak, kata kunci, dan satu file PDF atau DOCX tanpa akun.

1. Pengirim mengisi formulir dan mendapat nomor referensi `PUB-...`. Editor belum menerima apa pun.
2. Sistem mengirim email berisi tautan bertanda tangan yang berlaku 7 hari.
3. Pengirim membuka tautan dan membuat password. Jika email itu sudah punya akun, pengirim harus login dulu.
4. Sistem mengaktifkan akun Author dan membuat naskah `ICSMI-...` berstatus Submitted. Editor mendapat email, lalu naskah mengikuti alur review biasa.

Alur ini **wajib SMTP yang berfungsi**. Saat lokal, tautan konfirmasi ditulis ke `storage/logs/laravel.log`.

**Editor > Public Submissions** menampilkan semua kiriman beserta statusnya. Kiriman yang tidak dikonfirmasi dalam 7 hari dihapus oleh `php artisan icsmi:purge-unconfirmed`, yang berjalan harian lewat scheduler. Pasang cron ini di server:

```
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

## Konten situs

Admin dan editor mengubah isi situs dari panel, tanpa menyentuh kode.

- **Media Library** (`/library`, admin dan editor). Gambar JPG, PNG, atau WebP maksimal 5 MB, dan PDF maksimal 20 MB. Jenis file dicek dari isinya, bukan dari nama. Gambar dibuat ulang di server, sehingga kode yang disisipkan di belakang file ikut hilang. Setiap gambar wajib punya teks alternatif.
- **Halaman** (`/admin/pages`, admin dan editor). Status Draf, Terjadwal, atau Terbit. Setiap simpan membuat revisi yang tidak bisa diubah atau dihapus. Pratinjau hanya bisa dibuka staf yang sudah login. Mengubah alamat halaman memutus tautan lama.
- **Menu dan beranda** (`/admin/menu` dan `/admin/home-blocks`, hanya admin). Tautan hanya boleh berupa path di situs, `https://`, atau `mailto:`. Setiap blok beranda bisa diberi jadwal tayang.
- **Pengumuman** (`/admin/announcements`, admin dan editor). Kategori Pengumuman, Berita, atau Tenggat, dan tanggal berakhir.
- **Ekspor dan impor** (`/admin/content`, hanya admin). Ekspor menghasilkan satu file JSON tanpa akun atau password. Impor selalu lewat pratinjau, dan baru berubah setelah dikonfirmasi. **Menu dan beranda diganti seluruhnya oleh isi file.** Ekspor dulu sebelum impor. File maksimal 2 MB, dan gambar tidak ikut diekspor atau diimpor.

## Menjalankan di komputer lokal

Butuh PHP 8.3 atau lebih baru, Composer, dan Node.js.

```bash
composer install
npm install
npm run build
cp .env.example .env
```

Ubah nilai ini di `.env` untuk lokal:

```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
MAIL_MAILER=log
```

Lalu siapkan database dan jalankan server:

```bash
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

`--seed` membuat empat akun contoh dan data contoh berlabel "Sample". Password akun contoh diambil dari `SAMPLE_ADMIN_PASSWORD`, `SAMPLE_EDITOR_PASSWORD`, `SAMPLE_REVIEWER_PASSWORD`, dan `SAMPLE_AUTHOR_PASSWORD` di `.env`. Jika kosong, password dibuat acak setiap kali seeder jalan. Isi keempatnya untuk password yang tetap. Saat lokal, email ditulis ke `storage/logs/laravel.log`.

## Test

```bash
php artisan test
composer audit
npm audit
```

## Deploy ke hosting

Syarat: PHP 8.3 atau lebih baru dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `zip`, `gd` (untuk membuat ulang gambar), dan HTTPS.

1. Upload proyek ke folder di luar folder publik hosting, lalu arahkan **document root ke folder `public/`**. Ini wajib. Jika document root mengarah ke folder proyek, file `.env` dan database bisa diunduh siapa saja. Jangan ikut mengunggah `.env` lokal, `public/hot`, atau `sample-accounts.txt`.
2. Pasang dependensi. Jika hosting tidak punya Node.js, jalankan `npm run build` di komputer Anda dan upload folder `public/build`.

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

3. Salin `.env.example` menjadi `.env`, lalu isi:
   - `APP_URL` dengan alamat HTTPS situs. Permintaan dengan host lain akan ditolak.
   - `MAIL_*` dengan akun SMTP. Tanpa ini, email verifikasi dan notifikasi tidak terkirim.
   - `TRUSTED_PROXIES` jika situs berada di belakang reverse proxy atau load balancer.
   - Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.
4. Siapkan aplikasi. Di mode production, seeder hanya membuat halaman dasar, menu, dan beranda awal, tanpa akun atau data contoh.

   ```bash
   php artisan key:generate --force
   touch database/database.sqlite
   php artisan migrate --force
   php artisan db:seed --force
   php artisan optimize
   ```

5. Pastikan web server bisa menulis ke `storage/`, `bootstrap/cache/`, dan `database/`. SQLite perlu izin tulis pada foldernya, bukan hanya pada filenya.
6. Daftar lewat situs dengan email Anda, lalu jadikan akun itu admin:

   ```bash
   php artisan icsmi:make-admin email-anda@contoh.ac.id
   ```

7. Isi halaman lewat **Admin > Pages**, lalu buat prosiding lewat **Editor > Proceedings**.

### Batas upload

Gambar di Media Library maksimal 5 MB, dan PDF maksimal 20 MB. PHP dan web server punya batas sendiri.

- `public/.user.ini` menaikkan `upload_max_filesize` dan `post_max_size` pada hosting PHP-FPM atau CGI. Jika hosting mengabaikannya, atur lewat panel hosting.
- Nginx juga perlu `client_max_body_size 45m;`.
- `php artisan serve` tidak membaca `.user.ini`, jadi batas lokal mengikuti `php.ini`.

### Setelah mengubah kode atau `.env`

```bash
php artisan migrate --force
php artisan optimize
```

## Backup dan restore

Data ada di dua tempat, dan keduanya wajib di-backup:

- `database/database.sqlite`: seluruh data.
- `storage/app/private/`: naskah dan PDF artikel.

Simpan `.env` juga secara terpisah. `APP_KEY` di dalamnya diperlukan untuk membaca sesi terenkripsi.

### Backup

Jangan menyalin file database dengan `cp` saat situs berjalan, karena mode WAL bisa menyimpan data terbaru di file terpisah. Gunakan perintah ini:

```bash
sqlite3 database/database.sqlite ".backup 'backup/icsmi-$(date +%F).sqlite'"
sqlite3 backup/icsmi-$(date +%F).sqlite "PRAGMA integrity_check;"
tar -czf backup/files-$(date +%F).tar.gz storage/app/private
```

Hasil pemeriksaan harus `ok`. Simpan folder `backup/` di luar server, dan jadwalkan perintah ini lewat cron.

### Restore

Hentikan akses ke situs lebih dulu, lalu timpa database dan hapus file WAL lamanya:

```bash
php artisan down
cp backup/icsmi-TANGGAL.sqlite database/database.sqlite
rm -f database/database.sqlite-wal database/database.sqlite-shm
tar -xzf backup/files-TANGGAL.tar.gz
php artisan migrate --force
php artisan up
```

## Pindah ke MySQL atau PostgreSQL

Akses data memakai Eloquent, Query Builder, dan SQL standar, sehingga perpindahan seharusnya hanya perlu mengubah `DB_*` di `.env`, menjalankan migrasi, dan memindahkan data. Skenario ini **belum diuji**. Jalankan seluruh test pada database tujuan sebelum dipakai.

## Batasan yang diketahui

- Biaya dan pendaftaran peserta konferensi hanya berupa halaman informasi. Belum ada formulir pendaftaran atau pembayaran.
- Nama penulis di dalam isi atau properti dokumen tidak dibersihkan otomatis. Editor perlu memeriksa file anonim.
- Belum ada fitur menonaktifkan akun.
- Email dikirim langsung saat permintaan diproses, jadi SMTP yang lambat ikut memperlambat halaman.
- Jumlah tayang dan unduhan ikut menghitung kunjungan bot.
- Impor konten mengganti menu dan beranda seluruhnya. Ekspor dulu sebelum impor.
- PDF di Media Library tidak dibersihkan dari skrip, dan belum dipindai antivirus.
- Mengubah alamat halaman memutus tautan lama. Belum ada pengalihan otomatis.
- Halaman yang dihapus tidak punya tampilan pemulihan, meskipun riwayatnya tersimpan.
