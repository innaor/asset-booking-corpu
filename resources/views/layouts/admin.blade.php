<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — Peminjaman Aset')</title>

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Global Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- Per-page styles --}}
    @stack('styles')
</head>
<body>

<div class="app-layout">

    {{-- ===== SIDEBAR ADMIN ===== --}}
    <aside class="sidebar" id="sidebar">

        <a href="/admin/dashboard" class="sidebar-brand" style="text-decoration:none;">
            <i class="bi bi-shield-check" style="font-size:22px; color:rgba(255,255,255,0.90);"></i>
            <div>
                <div class="sidebar-brand-text">Peminjaman Aset</div>
                <div class="sidebar-brand-sub">Panel Admin</div>
            </div>
        </a>

        <nav class="sidebar-nav">

            <span class="sidebar-label">Menu</span>

            <a href="/admin/dashboard"
               class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i>
                Dashboard
            </a>

            <a href="/admin/assets"
               class="sidebar-link {{ request()->is('admin/assets*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                Data Aset
            </a>

            <a href="/admin/booking"
               class="sidebar-link {{ request()->is('admin/booking*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-check"></i>
                Data Booking
            </a>

            <a href="/admin/users"
                class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                User Management
            </a>

            <a href="/admin/activity-log"
                class="sidebar-link {{ request()->is('admin/activity-log*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                Activity Log
            </a>

            <div class="sidebar-divider"></div>

            <a href="/logout" class="sidebar-link logout">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>

        </nav>

    </aside>

    {{-- Overlay mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="main-content">

        {{-- Topbar mobile --}}
        <header class="topbar">
            <button class="topbar-hamburger" id="hamburgerBtn" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>
            <a href="/admin/dashboard" class="topbar-brand" style="text-decoration:none;">
                Peminjaman Aset
            </a>
            <div style="width:34px;"></div>
        </header>

        <main class="page-content">
            @yield('content')
        </main>

    </div>

</div>

@stack('scripts')

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

    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
</script>

</body>
</html>