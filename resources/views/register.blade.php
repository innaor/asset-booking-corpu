<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Peminjaman Aset</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>

<div class="auth-layout">

    {{-- ===== VISUAL PANEL (KIRI) ===== --}}
    <div class="auth-visual">

        <div class="auth-slide auth-slide-brand">
            <div class="auth-slide-bg"></div>
            <div class="auth-slide-overlay"></div>

            <div class="auth-slide-brand-content">
                <img src="{{ asset('images/logo-white-corpu.png') }}" alt="Telkom Corpu" class="auth-slide-brand-logo">
                <p>Kelola peminjaman aset kantor dengan mudah, cepat, dan transparan — kapan saja Anda butuhkan.</p>
            </div>
        </div>

        <div class="auth-slide">
            <div class="auth-slide-bg" style="background-image:url('{{ asset('images/auth/pesona-edu.jpg') }}');"></div>
            <div class="auth-slide-overlay"></div>
            <div class="auth-slide-caption">
                <span class="auth-slide-tag">Pesona Edu</span>
                <p>Ruang kelas nyaman untuk pelatihan dan sesi belajar tim Anda.</p>
            </div>
        </div>

        <div class="auth-slide">
            <div class="auth-slide-bg" style="background-image:url('{{ asset('images/auth/indigo-hall.jpg') }}');"></div>
            <div class="auth-slide-overlay"></div>
            <div class="auth-slide-caption">
                <span class="auth-slide-tag">Indigo Hall</span>
                <p>Satu ruang untuk seminar, workshop, dan momen berkumpul yang lebih besar.</p>
            </div>
        </div>

        <div class="auth-slide">
            <div class="auth-slide-bg" style="background-image:url('{{ asset('images/auth/laptop.jpg') }}');"></div>
            <div class="auth-slide-overlay"></div>
            <div class="auth-slide-caption">
                <span class="auth-slide-tag">Perlengkapan</span>
                <p>Pinjam laptop, kamera, dan perlengkapan penunjang kerja lainnya dengan sekali klik.</p>
            </div>
        </div>

    </div>

    {{-- ===== FORM PANEL (KANAN) ===== --}}
    <div class="auth-form-panel">
        <div class="auth-form-box">

            <div class="auth-form-header">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="auth-form-icon-img">
                <h1 class="auth-form-title">Daftar Akun</h1>
                <p class="auth-form-subtitle">Isi data di bawah untuk membuat akun baru</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <div style="display:flex; align-items:flex-start; gap:8px;">
                        <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0; margin-top:2px;"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <p style="margin:0 0 4px;">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="/register">
                @csrf

                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">No HP</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" required>
                        <option value="">— Pilih —</option>
                        <option value="karyawan" {{ old('status')=='karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="mentee" {{ old('status')=='mentee' ? 'selected' : '' }}>Mentee</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <button type="submit" class="btn btn-primary auth-submit-btn">
                    <i class="bi bi-person-check"></i>
                    Daftar
                </button>

                <p class="auth-switch-text">
                    Sudah punya akun? <a href="/login">Login di sini</a>
                </p>
            </form>

        </div>
    </div>

</div>

</body>
</html>