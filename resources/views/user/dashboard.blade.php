@extends('layouts.user')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Selamat datang, {{ auth()->user()->name }} 👋</p>
    </div>
    <button class="btn btn-primary" id="btnOpenModal">
        <i class="bi bi-plus-circle"></i>
        Booking Ruangan
    </button>
</div>

{{-- Schedule Card --}}
<div class="card">

    {{-- Toolbar Filter --}}
    <div class="schedule-toolbar">

        <div class="search-box">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="searchAset" class="search-input" placeholder="Cari aset...">
        </div>

        <select id="filterKategori" class="filter-select">
            <option value="">Semua Tipe</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">
                    {{ $category->category_name == 'Aset Bergerak' ? 'Perlengkapan'
                       : ($category->category_name == 'Aset Tidak Bergerak' ? 'Ruangan'
                       : $category->category_name) }}
                </option>
            @endforeach
        </select>

        <div class="toolbar-right">
            <label class="date-label">
                <i class="bi bi-calendar3"></i>
                Tanggal
            </label>
            <input type="date" id="filterTanggal" class="filter-date" value="{{ $date }}">
        </div>

    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="schedule-table" id="scheduleTable">
            <thead>
                <tr>
                    <th class="asset-name-col">Aset</th>
                    @for($h = 8; $h <= 16; $h++)
                        <th>{{ sprintf('%02d:00', $h) }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody id="scheduleBody">
            @foreach($assets as $asset)
            <tr data-asset-name="{{ strtolower($asset->name) }}"
                data-category-id="{{ $asset->subcategory->category_id ?? '' }}">

                <td class="asset-name-col">{{ $asset->name }}</td>

                @php $skipUntil = []; @endphp

                @for($hour = 8; $hour <= 16; $hour++)
                    @php
                        if (in_array($hour, $skipUntil)) continue;

                        $booking = $bookings->first(function($b) use ($asset, $hour) {
                            $start = (int) date('H', strtotime($b->start_time));
                            return $b->asset_id == $asset->id && $start === $hour;
                        });

                        if ($booking) {
                            $start    = (int) date('H', strtotime($booking->start_time));
                            $end      = (int) date('H', strtotime($booking->end_time));
                            $colspan  = min($end - $start, 17 - $hour);
                            for ($s = $hour + 1; $s < $hour + $colspan; $s++) {
                                $skipUntil[] = $s;
                            }
                            $slotClass = 'time-slot slot-booked' . match($booking->status) {
                                'pending'   => ' slot-pending',
                                'approved'  => ' slot-approved',
                                'ongoing'   => ' slot-ongoing',
                                'completed' => ' slot-completed',
                                'rejected'  => ' slot-rejected',
                                'cancelled' => ' slot-cancelled',
                                default     => '',
                            };
                        } else {
                            $colspan   = 1;
                            $slotClass = 'time-slot';
                        }
                    @endphp

                    <td class="{{ $slotClass }}" @if($colspan > 1) colspan="{{ $colspan }}" @endif>
                        @if($booking)
                            <div class="slot-booking-info">
                                {{ $booking->kepentingan ?? 'Tidak ada keterangan' }}
                                oleh {{ $booking->user->name ?? $booking->guest_name }}
                            </div>
                            <div class="slot-time">
                                {{ date('H:i', strtotime($booking->start_time)) }}–{{ date('H:i', strtotime($booking->end_time)) }}
                            </div>
                            <span class="slot-status">{{ ucfirst($booking->status) }}</span>
                        @else
                            <span class="slot-empty">—</span>
                        @endif
                    </td>
                @endfor
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div id="noResult" style="display:none; text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
        <i class="bi bi-search" style="font-size:28px; display:block; margin-bottom:8px;"></i>
        Tidak ada aset yang cocok dengan pencarian.
    </div>

</div>


{{-- ===================================================
     MODAL: BOOKING RUANGAN
     =================================================== --}}
<div class="modal-overlay" id="modalBooking" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">

        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="modalTitle">
                    <i class="bi bi-calendar2-plus" style="color:var(--color-primary-400);"></i>
                    Buat Booking Baru
                </h2>
                <p class="modal-subtitle">Isi form di bawah untuk mengajukan peminjaman aset</p>
            </div>
            <button class="modal-close" id="btnCloseModal" aria-label="Tutup modal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom:var(--space-md);">
                <div style="display:flex; align-items:flex-start; gap:8px;">
                    <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p style="margin:0 0 4px;">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="/user/booking/store">
            @csrf

            <div class="form-group">
                <label for="asset_id">Pilih Aset</label>
                <select name="asset_id" id="asset_id" required>
                    <option value="">— Pilih Aset —</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="date">Tanggal Peminjaman</label>
                <input type="date" name="date" id="date"
                       value="{{ old('date') }}"
                       min="{{ date('Y-m-d') }}"
                       required>
            </div>
            
            <div class="form-group">
                <label for="kepentingan">Kepentingan</label>
                <textarea name="kepentingan" id="kepentingan" rows="2"
                        placeholder="Contoh: Rapat internal divisi TI"
                        required>{{ old('kepentingan') }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
                <div class="form-group">
                    <label for="start_time">Jam Mulai</label>
                    <select name="start_time" id="start_time" required>
                        <option value="" selected>-</option>
                        @for($i = 8; $i <= 16; $i++)
                            <option value="{{ sprintf('%02d:00', $i) }}">
                                {{ sprintf('%02d:00', $i) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label for="end_time">Jam Selesai</label>
                    <select name="end_time" id="end_time" required>

                        <option value="" selected>-</option>

                        @for($i = 9; $i <= 17; $i++)
                            <option value="{{ sprintf('%02d:00', $i) }}">
                                {{ sprintf('%02d:00', $i) }}
                            </option>
                        @endfor

                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Simpan Booking
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelModal">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: BOOKING BERHASIL
     =================================================== --}}
@if(session('booking_success'))
<div class="modal-overlay active" id="modalSuccess" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:400px; text-align:center; padding:var(--space-xl);">

        <div style="margin-bottom:var(--space-md);">
            <i class="bi bi-check-circle-fill"
               style="font-size:64px; color:var(--color-success-btn); display:block;"></i>
        </div>

        <h2 style="font-size:20px; font-weight:600; color:var(--color-gray-900); margin-bottom:8px;">
            Booking Berhasil!
        </h2>

        <p style="color:var(--color-gray-600); margin-bottom:var(--space-lg); font-size:14px;">
            {{ session('booking_success') }}
        </p>

        <button class="btn btn-success"
                style="justify-content:center; width:100%;"
                onclick="document.getElementById('modalSuccess').classList.remove('active'); document.body.style.overflow='';">
            <i class="bi bi-check"></i>
            Oke, Mengerti
        </button>

    </div>
</div>
@endif


@push('scripts')
<script>
    // ============================================================
    // MODAL BOOKING
    // ============================================================
    const modal     = document.getElementById('modalBooking');
    const btnOpen   = document.getElementById('btnOpenModal');
    const btnClose  = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancelModal');

    function openModal()  { modal.classList.add('active');    document.body.style.overflow = 'hidden'; }
    function closeModal() { modal.classList.remove('active'); document.body.style.overflow = ''; }

    btnClose.addEventListener('click', closeModal);
    btnCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });

    @if($errors->any())
        openModal();
    @endif

    // ============================================================
    // VALIDASI JAM
    // ============================================================
    const nowHour    = {{ date('G') }};
    const todayStr   = '{{ date('Y-m-d') }}';
    const dateInput  = document.getElementById('date');
    const startSelect= document.getElementById('start_time');
    const endSelect  = document.getElementById('end_time');

    function updateDisabledHours() {
        const isToday = dateInput.value === todayStr;

        Array.from(startSelect.options).forEach(function(opt) {
            const h = parseInt(opt.value.split(':')[0]);
            opt.disabled = isToday && h <= nowHour;
            if (opt.disabled && opt.selected) {
                const firstValid = Array.from(startSelect.options).find(o => !o.disabled);
                if (firstValid) firstValid.selected = true;
            }
        });

        const startH = parseInt(startSelect.value.split(':')[0]);
        Array.from(endSelect.options).forEach(function(opt) {
            const h = parseInt(opt.value.split(':')[0]);
            opt.disabled = h <= startH || (isToday && h <= nowHour + 1);
        });
    }

    dateInput.addEventListener('change', updateDisabledHours);
    startSelect.addEventListener('change', updateDisabledHours);

    btnOpen.addEventListener('click', function() {
        openModal();
        updateDisabledHours();
    });

    // ============================================================
    // FILTER TANGGAL (auto-reload)
    // ============================================================
    document.getElementById('filterTanggal').addEventListener('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('date', this.value);
        window.location.href = url.toString();
    });

    // ============================================================
    // FILTER SEARCH + KATEGORI (realtime JS)
    // ============================================================
    const searchInput    = document.getElementById('searchAset');
    const filterKategori = document.getElementById('filterKategori');
    const rows           = document.querySelectorAll('#scheduleBody tr');
    const noResult       = document.getElementById('noResult');
    const tableWrapper   = document.querySelector('.table-wrapper');

    function applyFilters() {
        const keyword    = searchInput.value.toLowerCase().trim();
        const kategoriId = filterKategori.value;
        let visibleCount = 0;

        rows.forEach(function(row) {
            const matchSearch   = keyword === '' || (row.dataset.assetName || '').includes(keyword);
            const matchKategori = kategoriId === '' || (row.dataset.categoryId || '') === kategoriId;
            row.style.display   = (matchSearch && matchKategori) ? '' : 'none';
            if (matchSearch && matchKategori) visibleCount++;
        });

        tableWrapper.style.display = visibleCount === 0 ? 'none'  : '';
        noResult.style.display     = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', applyFilters);
    filterKategori.addEventListener('change', applyFilters);

    function updateAvailableTime() {

        const dateInput = document.getElementById('date');
        const startTime = document.getElementById('start_time');
        const endTime = document.getElementById('end_time');

        if (!dateInput || !startTime || !endTime) return;

        const selectedDate = dateInput.value;

        const now = new Date();

        const today =
            now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0');

        // Reset semua option
        [...startTime.options].forEach(option => option.disabled = false);
        [...endTime.options].forEach(option => option.disabled = false);

        // Kalau bukan hari ini, selesai
        if (selectedDate !== today) return;

        const currentHour = now.getHours();

        // Disable jam yang sudah lewat
        [...startTime.options].forEach(option => {

            const hour = parseInt(option.value.substring(0,2));

            if(hour <= currentHour){
                option.disabled = true;
            }

        });

        [...endTime.options].forEach(option => {

            const hour = parseInt(option.value.substring(0,2));

            if(hour <= currentHour + 1){
                option.disabled = true;
            }

        });

    }

    document.getElementById('date')
    .addEventListener('change', updateAvailableTime);

    document.getElementById('btnOpenModal')
    .addEventListener('click', function(){

        setTimeout(updateAvailableTime,100);

    });

    window.addEventListener('load', updateAvailableTime);
</script>
@endpush

@endsection