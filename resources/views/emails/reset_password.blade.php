<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
</head>

<body>
    <h1>Halo!</h1>
    <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>
    <p>Silakan klik tombol di bawah ini untuk mereset password Anda:</p>

    <a href="{{ $resetUrl }}"
        style="background-color: #4CAF50; color: white; padding: 14px 25px; text-align: center; text-decoration: none; display: inline-block;">
        Reset Password
    </a>

    <p>Jika Anda tidak merasa meminta reset password, abaikan saja email ini.</p>
    <p>Terima kasih.</p>
</body>

</html>
