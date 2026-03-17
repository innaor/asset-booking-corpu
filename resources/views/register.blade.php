<!DOCTYPE html>
<html>
<head>
    <title>Register - Peminjaman Aset</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>

<div class="container">
    <div class="card auth-card">

        <h2 class="auth-title">Daftar Akun</h2>

        <form method="POST" action="/register">
            @csrf

            <label>Nama</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>No HP</label>
            <input type="text" name="phone" required>

            <label>Status</label>
            <select name="status" required>
                <option value="">-- Pilih --</option>
                <option value="karyawan">Karyawan</option>
                <option value="mentee">Mentee</option>
            </select>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-primary">Daftar</button>
        </form>

        <p style="text-align:center; margin-top:15px;">
            Sudah punya akun? <a href="/login">Login</a>
        </p>

    </div>
</div>

</body>
</html>