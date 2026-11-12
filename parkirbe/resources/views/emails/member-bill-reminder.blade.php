<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Iuran Member Parkir</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f1eb; margin: 0; padding: 24px; color: #0a0a0a;">
    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border: 3px solid #0a0a0a; border-radius: 20px; box-shadow: 6px 6px 0 #0a0a0a; overflow: hidden;">
        
        <!-- Header -->
        <div style="background-color: #e4ff3a; border-bottom: 3px solid #0a0a0a; padding: 20px 24px;">
            <h1 style="margin: 0; font-size: 18px; font-weight: 900; letter-spacing: -0.5px; color: #0a0a0a;">
                E-PARKIR PLAZA ANDALAS
            </h1>
            <p style="margin: 4px 0 0; font-size: 12px; font-weight: 700; color: #333333;">
                Pemberitahuan & Pengingat Masa Berlaku Member
            </p>
        </div>

        <!-- Body -->
        <div style="padding: 24px;">
            <p style="font-size: 14px; margin-top: 0;">Halo Bpk/Ibu <strong>{{ $member->name }}</strong>,</p>
            <p style="font-size: 13px; line-height: 1.5; color: #333333;">
                Masa berlaku kartu langganan parkir resmi Anda di <strong>Plaza Andalas Padang</strong> telah habis / menunggu perpanjangan iuran bulanan.
            </p>

            <!-- Detail Box -->
            <div style="margin: 20px 0; padding: 18px; background-color: #f8fafc; border: 2px solid #0a0a0a; border-radius: 14px; box-shadow: 3px 3px 0 #0a0a0a;">
                <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #666666; font-size: 12px;">Kode Kartu Member:</td>
                        <td style="padding: 6px 0; font-weight: 900; font-family: monospace; text-align: right; color: #1e40af;">{{ $member->member_code }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #666666; font-size: 12px;">Nomor Plat:</td>
                        <td style="padding: 6px 0; font-weight: 900; font-family: monospace; text-align: right;">{{ $member->plate_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #666666; font-size: 12px;">Kategori:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right; text-transform: capitalize;">{{ $member->vehicle_category->value }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #666666; font-size: 12px;">Periode Tagihan:</td>
                        <td style="padding: 6px 0; font-weight: 700; text-align: right;">{{ $bill->period ? $bill->period->translatedFormat('F Y') : date('F Y') }}</td>
                    </tr>
                    <tr style="border-top: 2px dashed #0a0a0a;">
                        <td style="padding: 10px 0 0; font-weight: 800; font-size: 13px;">Total Iuran:</td>
                        <td style="padding: 10px 0 0; font-weight: 900; font-size: 18px; font-family: monospace; text-align: right; color: #0a0a0a;">
                            Rp {{ number_format($bill->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Petunjuk -->
            <div style="padding: 14px; background-color: #fffbeb; border: 2px solid #0a0a0a; border-radius: 12px; font-size: 12px; line-height: 1.5; color: #78350f;">
                <strong>Petunjuk Pembayaran:</strong>
                <p style="margin: 4px 0 0;">
                    Silakan datangi petugas kasir parkir Plaza Andalas untuk melakukan pembayaran iuran perpanjangan agar akses gerbang otomatis (portal masuk & keluar) tetap aktif tanpa kendala.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; border-top: 2px solid #0a0a0a; padding: 14px 24px; text-align: center; font-size: 11px; color: #666666;">
            &copy; {{ date('Y') }} E-Parkir Plaza Andalas Padang. Pesan ini dikirim secara otomatis.
        </div>
    </div>
</body>
</html>

