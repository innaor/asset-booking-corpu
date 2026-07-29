<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Peminjaman Aset</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="auth-layout">

    {{-- ===== VISUAL PANEL (KIRI) ===== --}}
    <div class="auth-visual">

        {{-- Slide 1: Brand intro --}}
        <div class="auth-slide auth-slide-brand">
            <div class="auth-slide-bg"></div>
            <div class="auth-slide-overlay"></div>

            <div class="auth-slide-brand-content">
                <img src="{{ asset('images/logo-white-corpu.png') }}" alt="Telkom Corpu" class="auth-slide-brand-logo">
                <p>Kelola peminjaman aset kantor dengan mudah, cepat, dan transparan — kapan saja Anda butuhkan.</p>
            </div>
        </div>

        {{-- Slide 2: Ruang Studio --}}
        <div class="auth-slide">
            <div class="auth-slide-bg" style="background-image:url('{{ asset('images/studio.jpg') }}');"></div>
            <div class="auth-slide-overlay"></div>
            <div class="auth-slide-caption">
                <span class="auth-slide-tag">Ruang Studio</span>
                <p>Rekam ide dan konten kreatif tim Anda di ruang studio yang siap pakai kapan saja.</p>
            </div>
        </div>

        {{-- Slide 3: Indigo Hall --}}
        <div class="auth-slide">
            <div class="auth-slide-bg" style="background-image:url('{{ asset('images/indigo-hall.jpg') }}');"></div>
            <div class="auth-slide-overlay"></div>
            <div class="auth-slide-caption">
                <span class="auth-slide-tag">Indigo Hall</span>
                <p>Satu ruang untuk seminar, workshop, dan momen berkumpul yang lebih besar.</p>
            </div>
        </div>

        {{-- Slide 4: Indigo Theater --}}
        <div class="auth-slide">
            <div class="auth-slide-bg" style="background-image:url('{{ asset('images/indigo-theater.jpg') }}');"></div>
            <div class="auth-slide-overlay"></div>
            <div class="auth-slide-caption">
                <span class="auth-slide-tag">Indigo Theater</span>
                <p>Presentasikan hasil kerja Anda dengan pengalaman audio-visual yang maksimal.</p>
            </div>
        </div>

    </div>

    {{-- ===== FORM PANEL (KANAN) ===== --}}
    <div class="auth-form-panel">
        <div class="auth-form-box">

            <div class="auth-form-header">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="auth-form-icon-img">
                <h1 class="auth-form-title">Masuk ke Akun</h1>
                <p class="auth-form-subtitle">Silakan masuk untuk mengelola peminjaman aset Anda</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email') }}"
                           placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password"
                           placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-primary auth-submit-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login
                </button>

                <p class="auth-switch-text">
                    Belum punya akun? <a href="/register">Daftar di sini</a>
                </p>
            </form>

        </div>
    </div>

</div>

</body>
</html>