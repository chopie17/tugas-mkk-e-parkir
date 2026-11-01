# DEBUGGING.md — Catatan Debugging E-Parkir Plaza Andalas

## Isu yang Ditemukan & Diperbaiki

---

### 🐛 BUG-01: Test Gagal karena Timing (Race Condition Test)

**Gejala**: T01, T05, T06 gagal dengan pesan `Failed asserting that 2 matches expected 1` atau `3 matches expected 2`.

**Root Cause**: Test menggunakan `now()->subHours(2)` untuk membuat tiket. Saat eksekusi test, selisih detik kecil (antara `now()` saat create dan saat panggil endpoint) membuat durasi menjadi sedikit di atas 120 menit, sehingga `ceil(121/60) = 3` bukan 2.

**Solusi**: Gunakan `Carbon::setTestNow()` dengan waktu eksplisit yang deterministik:
```php
$now = Carbon::create(2025, 6, 1, 10, 0, 0);
Carbon::setTestNow($now);
$ticket->entry_at = Carbon::create(2025, 6, 1, 8, 0, 0); // Tepat 2 jam
```

---

### 🐛 BUG-02: Duplikat processor() di ParkingTicket Model (False Alarm)

**Gejala**: `grep` menampilkan 2 baris `processor` dari 2 file berbeda (`MemberBill.php` dan `ParkingTicket.php`), terlihat seperti duplikat dalam 1 file.

**Root Cause**: Output `grep` menggabungkan hasil dari 2 file. Masing-masing file hanya punya 1 method `processor()`.

**Solusi**: Verifikasi dengan `cat` file individual. Tidak ada bug.

---

### 🐛 BUG-03: `proc_open(/dev/tty)` Error saat `php artisan test`

**Gejala**:
```
Symfony\Component\Process\Exception\ProcessStartFailedException
proc_open(/dev/tty): Failed to open stream: No such device or address
```

**Root Cause**: `php artisan test` mencoba membuka TTY untuk mode interaktif, namun lingkungan CI/sandbox tidak memiliki TTY.

**Solusi**: Jalankan langsung menggunakan `vendor/bin/phpunit` sebagai gantinya:
```bash
vendor/bin/phpunit --testdox
```

---

### 🐛 BUG-04: Nuxt Dev Server Tidak Bisa Background via `&`

**Gejala**: Nuxt dijalankan dengan `npm run dev &`, tapi proses langsung terkill (HTTP 000).

**Root Cause**: Di lingkungan sandbox, proses background dikelola berbeda. Proses child tidak tetap hidup setelah shell selesai.

**Solusi**: Jalankan dengan `RunPersistent: true` menggunakan tool `run_command`.

---

### 🐛 BUG-05: `PayBillRequest.php` Gagal Dibuat

**Gejala**: `No such file or directory` saat heredoc membuat file `PayBillRequest.php`.

**Root Cause**: Karakter `.` setelah `php` dalam nama file di-escape shell sebagai command. Terjadi saat path terlalu panjang di heredoc dengan `<<` operator.

**Solusi**: Pisahkan command, buat direktori terlebih dahulu dengan `mkdir -p`, lalu buat file.

---

## Tips Debugging

### Laravel Log
```bash
tail -f ~/mkk/parkirbe/storage/logs/laravel.log
```

### Test satu file
```bash
cd ~/mkk/parkirbe
vendor/bin/phpunit tests/Feature/TicketApiTest.php --testdox
```

### Cek routes API
```bash
php artisan route:list --path=api
```

### Reset database
```bash
php artisan migrate:fresh --seed
```

### Cek Nuxt error
```bash
# Port aktif
ss -tlnp | grep 3000
# Log Nuxt
cat /tmp/nuxt.log
```

### CORS Error (frontend tidak bisa panggil API)
Edit `~/mkk/parkirbe/config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000'],
```
Atau tambahkan di `.env`:
```
SANCTUM_STATEFUL_DOMAINS=localhost:3000
```
