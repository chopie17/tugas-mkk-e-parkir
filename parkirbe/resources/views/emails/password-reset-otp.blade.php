<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Pemulihan Password</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f1eb; margin: 0; padding: 24px; color: #0a0a0a;">
    <div style="max-width: 520px; margin: 0 auto; background-color: #ffffff; border: 3px solid #0a0a0a; border-radius: 20px; box-shadow: 6px 6px 0 #0a0a0a; overflow: hidden;">
        
        <!-- Header -->
        <div style="background-color: #e4ff3a; border-bottom: 3px solid #0a0a0a; padding: 20px 24px;">
            <h1 style="margin: 0; font-size: 18px; font-weight: 900; letter-spacing: -0.5px; color: #0a0a0a;">
                E-PARKIR PLAZA ANDALAS
            </h1>
            <p style="margin: 4px 0 0; font-size: 12px; font-weight: 700; color: #333333;">
                Sistem Manajemen Parkir & Akses Gerbang
            </p>
        </div>

        <!-- Body -->
        <div style="padding: 24px;">
            <p style="font-size: 14px; margin-top: 0;">Halo, <strong>{{ $userName }}</strong>,</p>
            <p style="font-size: 13px; line-height: 1.5; color: #333333;">
                Kami menerima permintaan pemulihan kata sandi untuk akun petugas/administrator E-Parkir Plaza Andalas Anda.
            </p>

            <div style="margin: 24px 0; padding: 20px; background-color: #f8fafc; border: 2px dashed #0a0a0a; border-radius: 14px; text-align: center;">
                <span style="display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #666666; margin-bottom: 8px;">
                    KODE VERIFIKASI OTP
                </span>
                <span style="display: inline-block; font-family: monospace; font-size: 32px; font-weight: 900; letter-spacing: 8px; color: #0a0a0a; background-color: #e4ff3a; padding: 8px 18px; border: 2px solid #0a0a0a; border-radius: 10px; box-shadow: 3px 3px 0 #0a0a0a;">
                    {{ $otpCode }}
                </span>
                <p style="font-size: 11px; font-weight: 600; color: #e11d48; margin: 12px 0 0;">
                    * Kode OTP ini berlaku selama {{ $expiresInMinutes }} menit. Jangan berikan kode ini kepada siapapun!
                </p>
            </div>

            <p style="font-size: 12px; line-height: 1.5; color: #555555;">
                Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini. Akun Anda tetap aman.
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; border-top: 2px solid #0a0a0a; padding: 14px 24px; text-align: center; font-size: 11px; color: #666666;">
            &copy; {{ date('Y') }} Plaza Andalas Padang. All rights reserved.
        </div>
    </div>
</body>
</html>

