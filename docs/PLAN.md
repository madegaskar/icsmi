# Plan penyusunan ICSMI

Dokumen ini mencatat status pekerjaan per 2026-11-08. Setiap item diberi status: **Selesai**, **Menunggu input**, atau **Terbuka**.

## Selesai

- Website publik, CMS, alur submission, workflow review, dan dashboard admin/editor/author/reviewer.
- Header publik (mobile dan desktop), breadcrumb, halaman 404 bermerek, `robots.txt` dengan noindex lewat `SITE_NOINDEX`.
- Saklar admin untuk membuka atau menutup registrasi (`e5d3f91`).
- Submission publik hanya menerima DOCX (`628dc82`).
- Cover proceeding di halaman archive, ukuran 222x309 (`e0a4948`, `4175188`).
- Deskripsi proceeding mendukung Markdown (`20f1906`).
- Perbaikan antislop: em dash di UI diganti `(none)` (`7e4f964`), gambar hero dan banner diberi ukuran (`8b621f3`).
- Audit keamanan run-2: 0 temuan confirmed, 4 lead needs validation (lihat `~/security-audit-skill/icsmi-baru/run-2/`).
- Test suite: 327 test lulus.

## Menunggu input dari pemilik

1. **Remote Git.** Repo belum punya remote. Perlu URL GitHub supaya commit bisa di-push.
2. **Konten klien.** Bagian "Accreditation" masih berisi teks placeholder. Perlu konten final.
3. **Logo dan favicon.** `favicon.ico` masih kosong. Perlu file logo.
4. **Keputusan atas lead keamanan** di `NEEDS-VALIDATION.md`:
   - Ubah email tanpa password (perlu keputusan apakah harus step-up).
   - Konflik kepentingan editor yang terikat ke email (perlu keputusan bind ke `user_id`).
   - Ganti password tidak mencabut sesi lain (perlu keputusan apakah pakai `AuthenticateSession`).
   - Saklar registrasi tidak berlaku untuk konfirmasi submission publik (perlu keputusan maksud produk).
5. **Tunnel devtunnel untuk client.** Port 8001 perlu diteruskan ulang dan di-set Public di tab PORTS VS Code.

## Terbuka (bisa dikerjakan tanpa input tambahan)

Sudah dikerjakan di perubahan belum di-commit: regex SafeLink ditutup dengan `\z`, file PDF artikel lama dihapus saat diganti, dan gambar di atas `media.max_image_pixels` (24 juta piksel) ditolak sebelum di-decode GD.


- Login sebagai author belum diverifikasi visualnya di browser.
- Form author dan revisi masih menerima PDF, padahal submission publik sudah DOCX saja. Perlu konfirmasi apakah ini juga mau dibatasi.

## Catatan operasional

- Aplikasi lokal memakai `APP_URL=http://localhost:8001`, `APP_DEBUG=false`, dan `SITE_NOINDEX=true`.
- Build asset dengan `npm run build` setiap kali class Tailwind baru ditambahkan di view.
- Tes: `php artisan test`.
