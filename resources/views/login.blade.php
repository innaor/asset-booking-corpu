<!DOCTYPE html>
<html>
<head>
    <title>Login - Asset Booking</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="wrapper">
    <div class="card">

        <h2>Asset Booking</h2>

        @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-primary">
                Login
            </button>
            <p style="text-align:center; margin-top:15px;">
                Belum punya akun? <a href="/register">Daftar</a>
            </p>
        </form>

    </div>
</div>

</body>
</html>