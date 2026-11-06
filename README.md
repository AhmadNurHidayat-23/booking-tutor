# Booking Tutor

**Teman Belajar** menyediakan pencarian tutor, kalender ketersediaan mingguan, reservasi sesi, perhitungan biaya, konfirmasi, dan riwayat belajar.

## Fitur

- Landing layanan tutor, profil, mata pelajaran, jenjang, pencarian, serta foto opsional.
- Registrasi/login pelajar dan akun petugas terpisah.
- Kalender mingguan dalam WIB, dengan durasi 30, 60, 90, atau 120 menit.
- Pengelolaan profil, tarif, jadwal, petunjuk pertemuan privat, serta aktivasi slot.
- Pencegahan jadwal tutor bertumpang tindih dan reservasi aktif ganda pada satu slot.
- Biaya dihitung server dari tarif/durasi, lalu disimpan pada reservasi sebagai snapshot.
- Reservasi menahan slot, petugas mengonfirmasi, dan sesi dapat ditandai selesai setelah waktu berakhir.
- Pembatalan melepas slot tanpa menghapus riwayat; pembatalan mandiri harus lebih dari 24 jam sebelum sesi.
- Detail pertemuan hanya ditampilkan kepada pemesan/petugas setelah konfirmasi.
- Riwayat dengan filter status, validasi, CSRF, pembatasan request, loading, pesan hasil, serta tampilan responsif.

## Teknologi

Laravel 13, Blade, Tailwind CSS 4, Vite 7, SQLite, PHPUnit, dan Playwright. Memerlukan PHP 8.3+ dengan GD, Composer, serta Node.js 22.12+. Versi dependensi dikunci melalui lockfile.

## Instalasi dan menjalankan

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite -ErrorAction SilentlyContinue
php artisan migrate
php artisan storage:link
npm ci
npm run build
php artisan staff:create
php artisan serve --host=127.0.0.1 --port=8061
```

Buka `http://127.0.0.1:8061`. Pelajar dapat mendaftar sendiri. Perintah `staff:create` meminta nama, email, dan kata sandi minimal 12 karakter; tidak ada akun/password bawaan. Seeder tidak mengarang profil tutor, kualifikasi, testimoni, rating, atau reservasi.

Petugas masuk lalu membuka **Kelola tutor & jadwal** untuk menambahkan profil tutor yang benar dan disetujui, mengatur tarif, serta menambahkan slot. Sesuaikan foto dan biodata dengan informasi yang telah diverifikasi pengelola. Jalankan `npm run dev` di terminal terpisah untuk pengembangan frontend.

Database khusus aplikasi ada di `database/database.sqlite`. Foto profil ada di disk publik `storage/app/public/tutors`. Keduanya diabaikan Git. Untuk MySQL, buat database baru khusus proyek dan ubah `DB_*` sebelum migrasi.

## Alur reservasi

Pelajar memilih tutor dan minggu ketersediaan, mengisi kebutuhan belajar, lalu memesan slot. Slot ditahan sejak reservasi berstatus menunggu; pengguna lain dengan tampilan kalender lama akan ditolak saat mencoba memesan slot yang sama.

Petugas meninjau reservasi dan mengonfirmasi dengan catatan. Petunjuk pertemuan, seperti tautan kelas, ditampilkan secara privat setelah konfirmasi. Biaya disimpan dari tarif saat pemesanan dan tidak berubah ketika tarif tutor diperbarui. Biaya dibulatkan ke rupiah terdekat sesuai durasi. Aplikasi tidak memproses maupun mengklaim pembayaran bank.

Pembatalan mandiri diperbolehkan lebih dari 24 jam sebelum mulai. Petugas dapat membantu pembatalan sesuai kebijakan layanan. Riwayat pembatalan tetap tersimpan dan slot dapat dipesan kembali. Sesi hanya dapat ditandai selesai oleh petugas setelah waktu selesai berlalu; status ini adalah pencatatan manual, bukan bukti otomatis bahwa pembelajaran berlangsung.

## Pengujian

```powershell
php artisan route:list --except-vendor
php artisan test
npm run build
npm run test:e2e
```

PHPUnit menggunakan SQLite dalam memori. Tes memeriksa constraint unik reservasi aktif, konflik slot, pelepasan setelah pembatalan, batas 24 jam, perhitungan biaya, snapshot tarif, hak akses, detail pertemuan privat, dan konfirmasi/penyelesaian.

Playwright menggunakan Microsoft Edge terpasang, port 8062, akun sementara dengan kata sandi acak, serta `database/.browser-test.sqlite` yang dibuat ulang khusus pengujian. Browser menguji pengelolaan tutor/jadwal, dua pelajar pada kalender lama, konfirmasi, pembatalan, pemesanan ulang, dan responsivitas. Screenshot/trace hanya tersimpan di `test-results/` yang diabaikan Git. Pengujian viewport bukan pengujian perangkat fisik.

## Operasional

Gunakan HTTPS, `APP_DEBUG=false`, document root `public/`, akun petugas terpercaya, serta pencadangan database/foto. Pengelola perlu memeriksa izin foto, biodata, kualifikasi, dan kebijakan layanan tutor. Foto landing adalah ilustrasi dari https://images.unsplash.com/photo-1434030216411-0b793f4b4173 dengan ketentuan https://unsplash.com/license. Berkas kode aplikasi maksimal 300 baris; tidak ada CSS/JavaScript inline pada Blade. Kredensial, database, screenshot, hasil build, vendor, dan node_modules tidak masuk repository.
