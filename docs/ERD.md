# ERD — Entity Relationship Diagram E-Parkir Plaza Andalas

## Diagram (Mermaid)

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string username UK
        string email UK
        string password
        enum role "petugas|super_admin"
        timestamps created_at updated_at
    }

    tariffs {
        bigint id PK
        enum type "non_member_hourly|member_monthly"
        enum vehicle_category "motor|mobil"
        int amount
        timestamps created_at updated_at
    }

    members {
        bigint id PK
        string member_code UK
        string name
        string plate_number UK
        enum vehicle_category "motor|mobil"
        int monthly_fee
        boolean is_active
        bigint created_by FK
        timestamps created_at updated_at deleted_at
    }

    member_bills {
        bigint id PK
        bigint member_id FK
        date period
        int amount
        enum status "belum_lunas|lunas"
        int cash_received NULL
        int change_amount NULL
        datetime paid_at NULL
        bigint processed_by FK NULL
        string invoice_number NULL
        timestamps created_at updated_at
    }

    member_entries {
        bigint id PK
        bigint member_id FK
        datetime scanned_at
        enum result "allowed|denied"
        string reason NULL
        timestamps created_at updated_at
    }

    parking_tickets {
        bigint id PK
        string token UK
        datetime entry_at
        datetime exit_at NULL
        enum status "active|paid"
        enum vehicle_category NULL "motor|mobil"
        string plate_number NULL
        int duration_hours NULL
        int fee NULL
        int cash_received NULL
        int change_amount NULL
        bigint processed_by FK NULL
        string invoice_number NULL
        timestamps created_at updated_at
    }

    password_reset_tokens {
        string email PK
        string token
        datetime created_at
    }

    users ||--o{ members : "created_by"
    users ||--o{ member_bills : "processed_by"
    users ||--o{ parking_tickets : "processed_by"
    members ||--o{ member_bills : "member_id"
    members ||--o{ member_entries : "member_id"
```

---

## Deskripsi Tabel

### `users`
Akun pengguna sistem (Petugas & Super Admin).

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| role  | ENUM | `petugas` atau `super_admin` |

---

### `tariffs`
Tarif parkir. 4 record default:

| Tipe | Kendaraan | Tarif |
|------|-----------|-------|
| non_member_hourly | motor | Rp 2.000/jam |
| non_member_hourly | mobil | Rp 5.000/jam |
| member_monthly | motor | Rp 150.000/bulan |
| member_monthly | mobil | Rp 200.000/bulan |

---

### `members`
Data anggota parkir berlangganan. Soft-delete diaktifkan.
- `member_code`: Format `MBR-XXXXXXXX` (random, unik)
- `plate_number`: Unique constraint
- `monthly_fee`: Disalin dari tariff saat daftar (historical)

---

### `member_bills`
Tagihan iuran bulanan. Unique constraint `(member_id, period)` mencegah duplikat.
- `period`: Selalu tanggal 1 bulan (`2024-11-01`, dsb)
- `invoice_number`: Format `INV-M-YYYYMM-XXXX`

---

### `member_entries`
Audit trail setiap scan kartu member. Menyimpan hasil (`allowed`/`denied`) beserta alasan.

---

### `parking_tickets`
Tiket karcis non-member. Satu siklus: `active` → `paid`.
- `token`: 6 karakter huruf besar + angka
- `invoice_number`: Format `INV-NM-YYYYMMDD-XXXX`
- Kolom vehicle, durasi, fee, dll: `NULL` saat pertama dibuat, terisi saat bayar

---

## Business Rules

1. **Tarif non-member**: `fee = ceil(duration_minutes / 60) × tariff_per_hour`, minimum 1 jam
2. **Scan member**: Diblokir jika `is_active = false` ATAU ada `member_bills.status = belum_lunas`
3. **Race condition**: Semua pembayaran menggunakan `DB::transaction()` + `lockForUpdate()`
4. **Invoice number**: Berurutan per hari (non-member) atau per bulan (member), reset setiap periode
