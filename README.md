# E-Parkir Plaza Andalas — README

> Sistem manajemen parkir berbasis web untuk **Plaza Andalas**.
> Stack: **Laravel 13** (API) + **Nuxt 4** (Frontend) + **MySQL 8.0**

---

## 🗂️ Struktur Proyek

```
~/mkk/
├── parkirbe/           # Backend Laravel 13 (API-only, Sanctum)
├── frontend/parkirfe/  # Frontend Nuxt 4 (Tailwind v4, Pinia)
├── docs/               # TESTING.md, DEBUGGING.md, ERD.md
└── README.md           # Dokumen ini
```

---

## ⚡ Quick Start

### 1. Prasyarat

| Tool | Versi |
|------|-------|
| PHP  | 8.4+  |
| Composer | 2.x |
| Node | 22+   |
| MySQL | 8.0+ |

### 2. Clone & Setup Backend

```bash
cd ~/mkk/parkirbe
cp .env.example .env          # Sudah dikonfigurasi: DB eparkir_db, user root:password123
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --port=8000
```

### 3. Setup Frontend

```bash
cd ~/mkk/frontend/parkirfe
npm install
npm run dev
# Buka: http://localhost:3000
```

---

## 🔑 Akun Demo

| Role        | Username     | Password     | Email                    |
|-------------|-------------|-------------|--------------------------|
| Super Admin | `superadmin` | `password123`| superadmin@parkir.test  |
| Petugas 1   | `petugas1`   | `password123`| petugas1@parkir.test    |
| Petugas 2   | `petugas2`   | `password123`| petugas2@parkir.test    |

---

## 📋 Fitur Lengkap

### Kiosk Gerbang Mandiri & Anti-Passback
- ✅ Landing Page Kiosk Gerbang Masuk & Keluar (`/`)
- ✅ Non-Member: Tombol ambil tiket masuk otomatis + auto-download karcis struk digital
- ✅ Non-Member: Tiket sekali pakai (hanya bisa scan keluar 1x, dicegah scan keluar ganda)
- ✅ Member: Scan QR / RFID / Input manual tanpa tiket berbayar (langsung buka gerbang)
- ✅ Member: Perlindungan Anti-Passback (Masuk hanya jika `OUT`, Keluar hanya jika `IN`)
- ✅ Simulator Palang Gerbang Interaktif (Barrier boom arm, signal lamp, sound synthesizer)

### Non-Member
- ✅ Cetak tiket masuk (token 6 karakter)
- ✅ Preview kalkulasi biaya (per jam, minimum 1 jam, ceil)
- ✅ Proses pembayaran (cash + kembalian otomatis)
- ✅ Invoice otomatis `INV-NM-YYYYMMDD-XXXX`

### Member
- ✅ Pendaftaran member (kode unik `MBR-XXXXXXXX`)
- ✅ Scan kartu QR (verifikasi status + tagihan)
- ✅ Tagihan bulanan otomatis (scheduler tiap tgl 1)
- ✅ Pembayaran tagihan + invoice `INV-M-YYYYMM-XXXX`

### Autentikasi
- ✅ Login dengan email **atau** username
- ✅ Bearer token (Sanctum)
- ✅ Lupa password (2-langkah, token 15 menit)
- ✅ Role: `petugas` & `super_admin`

### Laporan
- ✅ Laporan non-member (filter tanggal/cari/petugas)
- ✅ Laporan member (filter tanggal/cari/petugas)
- ✅ Ringkasan statistik hari ini (Super Admin)

### Admin (Super Admin only)
- ✅ Manajemen akun petugas (CRUD)
- ✅ Update tarif parkir
- ✅ Generate tagihan manual via API

---

## 🌐 API Endpoints Utama

Base URL: `http://localhost:8000/api`

```
POST   /gate/tickets/dispense    Kiosk: Ambil tiket masuk non-member (buka gerbang)
POST   /gate/members/scan        Kiosk: Scan member in/out (Anti-Passback)
POST   /gate/tickets/scan-exit   Kiosk: Validasi tiket keluar non-member (1x pakai)
POST   /gate/tickets/pay-exit    Kiosk: Bayar & selesaikan tiket keluar

POST   /login                    Auth: login
POST   /logout                   Auth: logout
GET    /me                       Auth: profil user
POST   /tickets                  Cetak tiket masuk
GET    /tickets/{token}          Detail tiket
GET    /tickets/{token}/preview  Preview biaya
POST   /tickets/{token}/pay      Bayar tiket
POST   /members/scan             Scan kartu member
GET    /members                  Daftar member
POST   /members                  Daftarkan member baru
GET    /bills                    Daftar tagihan
POST   /bills/{id}/pay           Bayar tagihan
GET    /reports/non-member       Laporan non-member
GET    /reports/member           Laporan member
GET    /reports/summary          Statistik hari ini
GET    /tariffs                  Daftar tarif (publik)
```

---

## 🧪 Unit Testing

```bash
cd ~/mkk/parkirbe
vendor/bin/phpunit --testdox
```

**20 test, 47 assertions — semua PASS** ✅

---

## ⏰ Scheduler

Tambahkan ke crontab server:
```cron
* * * * * php /home/chopie/mkk/parkirbe/artisan schedule:run >> /dev/null 2>&1
```

Tagihan bulanan di-generate otomatis setiap tanggal 1 pukul 00:05.

Atau generate manual:
```bash
php artisan parking:generate-bills
php artisan parking:generate-bills --period=2024-02-01
```
