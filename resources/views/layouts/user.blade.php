<!DOCTYPE html>
<html>
<head>
    <title>Peminjaman Aset</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">Peminjaman Aset</div>

        <div class="nav-links">
            <a href="/user/dashboard">Dashboard</a>
            <a href="#">Daftar Aset</a>
            <a href="#">Booking Saya</a>
            <a href="/logout" class="logout">Logout</a>
        </div>
    </div>
</nav>

<!-- CONTENT -->
<div class="container" style="margin-top:30px;">
    @yield('content')
</div>

</body>
</html>