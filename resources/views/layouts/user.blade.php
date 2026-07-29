<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Peminjaman Aset')</title>

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Global Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- Per-page styles --}}
    @stack('styles')
</head>
<body>

{{-- ===== APP LAYOUT ===== --}}
<div class="app-layout">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">

        {{-- Brand --}}
        <a href="/user/dashboard" class="sidebar-brand" style="text-decoration:none;">
            <img src="{{ asset('images/logo-icon.png') }}" alt="Logo" class="sidebar-brand-icon-img">
            <div>
                <div class="sidebar-brand-text">Peminjaman Aset</div>
                <div class="sidebar-brand-sub">Manajemen Booking</div>
            </div>
        </a>

        {{-- Navigation --}}
        <nav class="sidebar-nav">

            <span class="sidebar-label">Menu</span>

            <a href="/user/dashboard"
               class="sidebar-link {{ request()->is('user/dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i>
                Dashboard
            </a>

            <a href="/user/booking"
               class="sidebar-link {{ request()->is('user/booking*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-check"></i>
                Booking Saya
            </a>

            <a href="/user/bug-reports"
                class="sidebar-link {{ request()->is('user/bug-reports*') ? 'active' : '' }}">
                    <i class="bi bi-flag"></i>
                    Aduan Bug
            </a>

            <span class="sidebar-label">Akun</span>

            <a href="#"
               class="sidebar-link {{ request()->is('user/profile*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                Profil Saya
            </a>

            <div class="sidebar-divider"></div>

            <a href="/logout" class="sidebar-link logout">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>

        </nav>

    </aside>

    {{-- ===== OVERLAY (mobile) ===== --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="main-content">

        {{-- Topbar (mobile only) --}}
        <header class="topbar">
            <button class="topbar-hamburger" id="hamburgerBtn" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>
            <a href="/user/dashboard" class="topbar-brand" style="text-decoration:none;">
                Peminjaman Aset
            </a>
            <div style="width:34px;"></div>{{-- spacer untuk centering brand --}}
        </header>

        {{-- Page Content --}}
        <main class="page-content">
            @yield('content')
        </main>

    </div>

</div>

{{-- ===== PER-PAGE SCRIPTS ===== --}}
@stack('scripts')

{{-- ===== SIDEBAR TOGGLE SCRIPT ===== --}}
<script>
    const sidebar      = document.getElementById('sidebar');
    const overlay      = document.getElementById('sidebarOverlay');
    const hamburgerBtn = document.getElementById('hamburgerBtn');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    hamburgerBtn.addEventListener('click', openSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Tutup sidebar saat klik link di mobile
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
</script>

</body>
</html>