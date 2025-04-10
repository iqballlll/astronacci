<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Kode OTP Reset Password</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 20px;">
    <table width="100%"
        style="max-width: 600px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <tr>
            <td align="center" style="font-size: 24px; font-weight: bold; color: #333333;">
                🔐 Reset Password Request
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 0; font-size: 16px; color: #444;">
                Kamu baru saja meminta reset password. Gunakan kode OTP berikut untuk mengatur ulang password akun kamu:
            </td>
        </tr>
        <tr>
            <td align="center" style="padding: 20px 0;">
                <div
                    style="display: inline-block; font-size: 32px; font-weight: bold; color: #1a73e8; letter-spacing: 4px;">
                    {{ $otp }}
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 14px; color: #666;">
                Jangan bagikan kode ini kepada siapa pun. Kode ini akan kedaluwarsa dalam 10 menit.
            </td>
        </tr>
        <tr>
            <td style="padding-top: 30px; font-size: 14px; color: #999;">
                Salam hangat,<br />
                <strong>Tim Support {{ config('app.name') }}</strong>
            </td>
        </tr>
    </table>
</body>

</html>