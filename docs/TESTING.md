# TESTING.md — Panduan Pengujian E-Parkir Plaza Andalas

## Menjalankan Unit Test

```bash
cd ~/mkk/parkirbe
vendor/bin/phpunit --testdox
```

### Hasil yang Diharapkan

```
OK (20 tests, 47 assertions)
```

---

## Daftar Skenario Test (14 Skenario Inti)

| ID  | Suite   | Nama Test | Ekspektasi |
|-----|---------|-----------|-----------|
| T01 | Unit    | `fee_motor_1_hour` | 1 jam × Rp 2.000 = Rp 2.000 |
| T02 | Unit    | `fee_mobil_ceil_to_2_hours` | 1j 1m → ceil → 2 jam × Rp 5.000 = Rp 10.000 |
| T03 | Unit    | `minimum_1_hour` | 30 menit → minimum 1 jam = Rp 2.000 |
| T04 | Feature | `issue_ticket` | POST /tickets → 201, status=active, token ada |
| T05 | Feature | `preview_fee` | GET /tickets/{token}/preview → hours=2, fee=4000 |
| T06 | Feature | `pay_ticket_success` | POST pay → status=paid, kembalian benar |
| T07 | Feature | `pay_already_paid_ticket` | Tiket paid → 409 Conflict |
| T08 | Feature | `insufficient_cash` | Cash kurang → 422 Unprocessable |
| T09 | Feature | `pay_bill_success` | POST /bills/{id}/pay → lunas, kembalian benar, invoice ada |
| T10 | Feature | `pay_already_paid_bill` | Tagihan lunas → 409 Conflict |
| T11 | Feature | `insufficient_cash_for_bill` | Cash kurang → 422 |
| T12 | Feature | `scan_member_ok` | Member aktif, tidak ada tunggakan → allowed=true |
| T13 | Feature | `scan_member_with_unpaid_bill` | Ada tunggakan → allowed=false, 422 |
| T14 | Feature | `scan_inactive_member` | Member non-aktif → allowed=false, 422 |

---

## Testing API Manual (cURL)

### 1. Login
```bash
curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"login":"superadmin","password":"password123"}' | jq .
```

### 2. Cetak Tiket (simpan TOKEN dari response)
```bash
TOKEN="<paste_bearer_token>"
curl -s -X POST http://localhost:8000/api/tickets \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq .
```

### 3. Preview Biaya
```bash
TICKET_TOKEN="<token_tiket>"
curl -s "http://localhost:8000/api/tickets/$TICKET_TOKEN/preview?vehicle_category=motor" \
  -H "Authorization: Bearer $TOKEN" | jq .
```

### 4. Bayar Tiket
```bash
curl -s -X POST "http://localhost:8000/api/tickets/$TICKET_TOKEN/pay" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"vehicle_category":"motor","plate_number":"B 1234 ABC","cash_received":10000}' | jq .
```

### 5. Scan Kartu Member
```bash
curl -s -X POST http://localhost:8000/api/members/scan \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"member_code":"MBR-DEMO0001"}' | jq .
```

### 6. Bayar Tagihan Member
```bash
curl -s -X POST http://localhost:8000/api/bills/2/pay \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"cash_received":200000}' | jq .
```

---

## Test Database

Test menggunakan **SQLite in-memory** — tidak mempengaruhi database MySQL production.
Konfigurasi ada di `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
