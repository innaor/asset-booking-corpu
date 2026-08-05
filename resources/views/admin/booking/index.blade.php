@extends('layouts.admin')

@section('title', 'Data Booking')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/booking.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Data Booking</h1>
        <p class="page-subtitle">Kelola dan pantau status semua peminjaman aset</p>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ session('error') }}
    </div>
@endif

@php
    $today       = \Carbon\Carbon::today();
    $cntToday    = $bookings->filter(fn($b) => \Carbon\Carbon::parse($b->date)->isToday())->count();
    $cntUpcoming = $bookings->filter(fn($b) => \Carbon\Carbon::parse($b->date)->startOfDay()->gt($today))->count();
    $cntPast     = $bookings->filter(fn($b) => \Carbon\Carbon::parse($b->date)->startOfDay()->lt($today))->count();
    $cntAll      = $bookings->count();
@endphp

{{-- Filter Tabs --}}
<div class="booking-toolbar">
    <div class="booking-filter-tabs">
        <button class="filter-tab active" data-filter="all">
            <i class="bi bi-calendar3"></i> Semua <span class="tab-count">{{ $cntAll }}</span>
        </button>
        <button class="filter-tab" data-filter="today">
            <i class="bi bi-calendar-day"></i> Hari Ini <span class="tab-count">{{ $cntToday }}</span>
        </button>
        <button class="filter-tab" data-filter="upcoming">
            <i class="bi bi-calendar-plus"></i> Akan Datang <span class="tab-count">{{ $cntUpcoming }}</span>
        </button>
        <button class="filter-tab" data-filter="past">
            <i class="bi bi-calendar-minus"></i> Riwayat <span class="tab-count">{{ $cntPast }}</span>
        </button>
    </div>

    <div class="booking-toolbar-actions">
        <div class="booking-date-filter">
            <button type="button" class="booking-filter-btn" onclick="toggleBookingFilter()">
                <i class="bi bi-calendar3"></i>
                <span id="bookingFilterLabel">Filter</span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="booking-filter-dropdown" id="bookingFilterDropdown">
                <button onclick="applyDateFilter('all')">Semua Waktu</button>
                <button onclick="applyDateFilter('today')">Hari Ini</button>
                <button onclick="applyDateFilter('month')">Bulan Ini</button>
                <button onclick="applyDateFilter('lastMonth')">Bulan Lalu</button>
                <button onclick="applyDateFilter('3month')">3 Bulan Terakhir</button>
                <button onclick="applyDateFilter('year')">Tahun Ini</button>
                <hr>
                <button onclick="openCustomDateModal()">Custom...</button>
            </div>
        </div>

        <a href="{{ route('admin.booking.export', request()->query()) }}" class="booking-export-btn" title="Download Excel">
            <i class="bi bi-download"></i>
        </a>
    </div>
</div>

{{-- Table --}}
<div class="card" style="padding:0; overflow:hidden;">
    <div class="table-wrapper" id="tableWrapper" style="border:none; border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Nama Peminjam</th>
                    <th>Kontak</th>
                    <th>Aset</th>
                    <th>Kepentingan</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="bookingTableBody">
                @forelse($bookings as $booking)
                @php
                    $bookingDate = \Carbon\Carbon::parse($booking->date)->startOfDay();
                    $period = $bookingDate->isToday() ? 'today'
                            : ($bookingDate->gt($today) ? 'upcoming' : 'past');
                @endphp
                <tr data-period="{{ $period }}" data-date="{{ $booking->date }}">
                    <td style="font-weight:500;">{{ $booking->user->name ?? $booking->guest_name }}</td>
                    <td style="color:var(--color-gray-600);">{{ $booking->user->phone ?? $booking->guest_phone }}</td>
                    <td>{{ $booking->asset->name }}</td>
                    <td style="max-width:200px;">{{ $booking->kepentingan ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }}</td>
                    <td>
                        <span style="font-family:monospace; font-size:13px;">
                            {{ date('H:i', strtotime($booking->start_time)) }} – {{ date('H:i', strtotime($booking->end_time)) }}
                        </span>
                    </td>

                    {{-- Status --}}
                    <td>
                        @if(in_array($booking->status, ['approved', 'ongoing', 'completed']))
                            {{-- Status approved/ongoing/completed TIDAK BISA diubah manual --}}
                            <div class="booking-status booking-status-{{ $booking->status }} booking-status-static">
                                <span>
                                    @if($booking->status == 'approved') Approved
                                    @elseif($booking->status == 'ongoing') On Going
                                    @else Completed
                                    @endif
                                </span>
                            </div>
                        @else
                            <form method="POST" action="/admin/booking/update-status/{{ $booking->id }}">
                                @csrf
                                <div class="booking-status booking-status-{{ $booking->status }}" id="wrapper-{{ $booking->id }}">
                                    <select class="booking-status-select" name="status"
                                            onchange="updateStatusColor(this); this.form.submit();">
                                        <option value="pending" {{ $booking->status=='pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $booking->status=='approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="cancelled" {{ $booking->status=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </form>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td>
                        <div class="btn-group">
                            @if($booking->status == 'approved')
                                <button class="btn-icon btn-icon-primary" title="Check-in (serah terima barang)"
                                        onclick="openCheckinModal({{ $booking->id }}, '{{ $booking->asset->name }}', '{{ $booking->user->name ?? $booking->guest_name }}')">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                            @elseif($booking->status == 'ongoing')
                                <button class="btn-icon btn-icon-primary" title="Check-out (pengembalian barang)"
                                        onclick="openCheckoutModal({{ $booking->id }}, '{{ $booking->asset->name }}', '{{ $booking->user->name ?? $booking->guest_name }}')">
                                    <i class="bi bi-box-arrow-in-left"></i>
                                </button>
                            @elseif($booking->status == 'completed')
                                <button class="btn-icon" title="Lihat kondisi barang"
                                        onclick="openViewConditionModal(
                                            '{{ $booking->asset->name }}',
                                            '{{ $booking->checkin_condition }}',
                                            `{{ $booking->checkin_note }}`,
                                            '{{ $booking->checkin_photo ? asset('storage/'.$booking->checkin_photo) : '' }}',
                                            '{{ $booking->checkin_at ? \Carbon\Carbon::parse($booking->checkin_at)->format('d/m/Y H:i') : '-' }}',
                                            '{{ $booking->checkout_condition }}',
                                            `{{ $booking->checkout_note }}`,
                                            '{{ $booking->checkout_photo ? asset('storage/'.$booking->checkout_photo) : '' }}',
                                            '{{ $booking->checkout_at ? \Carbon\Carbon::parse($booking->checkout_at)->format('d/m/Y H:i') : '-' }}'
                                        )">
                                    <i class="bi bi-clipboard-check"></i>
                                </button>
                            @endif

                            @if($booking->status == 'pending')
                                <button class="btn-icon btn-icon-danger" title="Hapus booking"
                                        onclick="confirmDeleteAdmin(
                                            '{{ route('booking.destroy', $booking->id) }}',
                                            '{{ $booking->user->name ?? $booking->guest_name }}',
                                            '{{ $booking->asset->name }}',
                                            '{{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }}'
                                        )">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
                        <i class="bi bi-calendar-x" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        Belum ada data booking.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="noBookingResult" style="display:none; text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
        <i class="bi bi-calendar-x" style="font-size:32px; display:block; margin-bottom:8px;"></i>
        <span id="noBookingText">Tidak ada booking pada periode ini.</span>
    </div>
</div>


{{-- ===================================================
     MODAL: CHECK-IN (Serah Terima Barang)
     =================================================== --}}
<div class="modal-overlay" id="modalCheckin" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="checkinTitle">
                    <i class="bi bi-box-arrow-in-right" style="color:var(--color-primary-400);"></i>
                    Check-in Barang
                </h2>
                <p class="modal-subtitle">Catat kondisi barang saat diserahkan ke peminjam</p>
            </div>
            <button class="modal-close" onclick="closeModal('modalCheckin')" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" id="formCheckin" action="" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Kondisi Barang</label>
                <select name="checkin_condition" required>
                    <option value="">— Pilih Kondisi —</option>
                    <option value="baik">Baik</option>
                    <option value="rusak_ringan">Rusak Ringan</option>
                    <option value="rusak_berat">Rusak Berat</option>
                </select>
            </div>
            <div class="form-group">
                <label>Catatan <span style="font-weight:400; color:var(--color-gray-400);">(opsional)</span></label>
                <textarea name="checkin_note" rows="3" placeholder="Keterangan tambahan kondisi barang..."></textarea>
            </div>
            <div class="form-group">
                <label>Foto Bukti <span style="font-weight:400; color:var(--color-gray-400);">(opsional, maks 2MB)</span></label>
                <input type="file" name="checkin_photo" accept="image/*">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Konfirmasi Check-in
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCheckin')">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: CHECK-OUT (Pengembalian Barang)
     =================================================== --}}
<div class="modal-overlay" id="modalCheckout" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="checkoutTitle">
                    <i class="bi bi-box-arrow-in-left" style="color:var(--color-primary-400);"></i>
                    Check-out Barang
                </h2>
                <p class="modal-subtitle">Catat kondisi barang saat dikembalikan</p>
            </div>
            <button class="modal-close" onclick="closeModal('modalCheckout')" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" id="formCheckout" action="" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Kondisi Barang</label>
                <select name="checkout_condition" required>
                    <option value="">— Pilih Kondisi —</option>
                    <option value="baik">Baik</option>
                    <option value="rusak_ringan">Rusak Ringan</option>
                    <option value="rusak_berat">Rusak Berat</option>
                </select>
            </div>
            <div class="form-group">
                <label>Catatan <span style="font-weight:400; color:var(--color-gray-400);">(opsional)</span></label>
                <textarea name="checkout_note" rows="3" placeholder="Keterangan tambahan kondisi barang..."></textarea>
            </div>
            <div class="form-group">
                <label>Foto Bukti <span style="font-weight:400; color:var(--color-gray-400);">(opsional, maks 2MB)</span></label>
                <input type="file" name="checkout_photo" accept="image/*">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Konfirmasi Check-out
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCheckout')">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: LIHAT KONDISI (Read-only, untuk status Completed)
     =================================================== --}}
<div class="modal-overlay" id="modalViewCondition" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:640px;">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="viewConditionTitle">
                    <i class="bi bi-clipboard-check" style="color:var(--color-primary-400);"></i>
                    Riwayat Kondisi Barang
                </h2>
            </div>
            <button class="modal-close" onclick="closeModal('modalViewCondition')" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-lg);">

            {{-- Check-in --}}
            <div>
                <h3 style="font-size:14px; font-weight:600; margin-bottom:8px;">
                    <i class="bi bi-box-arrow-in-right"></i> Saat Check-in
                </h3>
                <p style="margin:0 0 8px; font-size:12px; color:var(--color-gray-400);" id="viewCheckinAt"></p>
                <p style="margin:0 0 4px;">Kondisi: <strong id="viewCheckinCondition"></strong></p>
                <p style="margin:0 0 8px; color:var(--color-gray-600); font-size:13px;" id="viewCheckinNote"></p>
                <img id="viewCheckinPhoto" src="" style="max-width:100%; border-radius:8px; display:none;">
            </div>

            {{-- Check-out --}}
            <div style="border-left:1px solid var(--color-gray-200); padding-left:var(--space-lg);">
                <h3 style="font-size:14px; font-weight:600; margin-bottom:8px;">
                    <i class="bi bi-box-arrow-in-left"></i> Saat Check-out
                </h3>
                <p style="margin:0 0 8px; font-size:12px; color:var(--color-gray-400);" id="viewCheckoutAt"></p>
                <p style="margin:0 0 4px;">Kondisi: <strong id="viewCheckoutCondition"></strong></p>
                <p style="margin:0 0 8px; color:var(--color-gray-600); font-size:13px;" id="viewCheckoutNote"></p>
                <img id="viewCheckoutPhoto" src="" style="max-width:100%; border-radius:8px; display:none;">
            </div>

        </div>

        <div class="modal-footer" style="margin-top:var(--space-lg);">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalViewCondition')">
                <i class="bi bi-x-circle"></i> Tutup
            </button>
        </div>
    </div>
</div>


{{-- ===================================================
     MODAL: KONFIRMASI HAPUS (ADMIN)
     =================================================== --}}
<div class="modal-overlay" id="modalDeleteAdmin" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:420px; text-align:center; padding:var(--space-xl);">
        <div style="margin-bottom:var(--space-md);">
            <i class="bi bi-trash-fill" style="font-size:56px; color:var(--color-danger-btn);"></i>
        </div>
        <h2 style="font-size:18px; font-weight:600; color:var(--color-gray-900); margin-bottom:8px;">Hapus Booking?</h2>
        <p id="deleteAdminInfo" style="color:var(--color-gray-600); font-size:13px; margin-bottom:var(--space-lg); line-height:1.6;">
            Data booking ini akan dihapus permanen.
        </p>
        <form id="formDeleteAdmin" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="display:flex; gap:var(--space-sm);">
                <button type="button" class="btn btn-secondary" style="flex:1; justify-content:center;"
                        onclick="document.getElementById('modalDeleteAdmin').classList.remove('active'); document.body.style.overflow='';">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
                <button type="submit" class="btn btn-danger" style="flex:1; justify-content:center;">
                    <i class="bi bi-trash"></i> Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>


@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    }

    // ---- Update warna wrapper saat dropdown status berubah ----
    function updateStatusColor(select) {
        const wrapper = select.closest('.booking-status');
        ['pending','approved','rejected','cancelled'].forEach(status => {
            wrapper.classList.remove('booking-status-' + status);
        });
        wrapper.classList.add('booking-status-' + select.value);
    }

    // ---- Check-in ----
    function openCheckinModal(id, assetName, userName) {
        document.getElementById('formCheckin').action = '/admin/booking/checkin/' + id;
        document.getElementById('checkinTitle').innerHTML =
            '<i class="bi bi-box-arrow-in-right" style="color:var(--color-primary-400);"></i> Check-in: ' + assetName + ' (' + userName + ')';
        openModal('modalCheckin');
    }
    document.getElementById('modalCheckin')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalCheckin');
    });

    // ---- Check-out ----
    function openCheckoutModal(id, assetName, userName) {
        document.getElementById('formCheckout').action = '/admin/booking/checkout/' + id;
        document.getElementById('checkoutTitle').innerHTML =
            '<i class="bi bi-box-arrow-in-left" style="color:var(--color-primary-400);"></i> Check-out: ' + assetName + ' (' + userName + ')';
        openModal('modalCheckout');
    }
    document.getElementById('modalCheckout')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalCheckout');
    });

    // ---- View Condition (read-only) ----
    const conditionLabels = { baik: 'Baik', rusak_ringan: 'Rusak Ringan', rusak_berat: 'Rusak Berat' };

    function openViewConditionModal(assetName, ciCondition, ciNote, ciPhoto, ciAt, coCondition, coNote, coPhoto, coAt) {
        document.getElementById('viewConditionTitle').innerHTML =
            '<i class="bi bi-clipboard-check" style="color:var(--color-primary-400);"></i> Riwayat Kondisi: ' + assetName;

        document.getElementById('viewCheckinAt').textContent = '(' + ciAt + ')';
        document.getElementById('viewCheckinCondition').textContent = conditionLabels[ciCondition] || '-';
        document.getElementById('viewCheckinNote').textContent = ciNote || '-';
        const ciImg = document.getElementById('viewCheckinPhoto');
        if (ciPhoto) { ciImg.src = ciPhoto; ciImg.style.display = 'block'; } else { ciImg.style.display = 'none'; }

        document.getElementById('viewCheckoutAt').textContent = '(' + coAt + ')';
        document.getElementById('viewCheckoutCondition').textContent = conditionLabels[coCondition] || '-';
        document.getElementById('viewCheckoutNote').textContent = coNote || '-';
        const coImg = document.getElementById('viewCheckoutPhoto');
        if (coPhoto) { coImg.src = coPhoto; coImg.style.display = 'block'; } else { coImg.style.display = 'none'; }

        openModal('modalViewCondition');
    }
    document.getElementById('modalViewCondition')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalViewCondition');
    });

    // ---- Modal Hapus Admin ----
    function confirmDeleteAdmin(action, nama, aset, tanggal) {
        document.getElementById('formDeleteAdmin').action = action;
        document.getElementById('deleteAdminInfo').innerHTML =
            'Booking <strong>' + aset + '</strong> atas nama <strong>' + nama + '</strong> pada <strong>' + tanggal + '</strong> akan dihapus permanen dan tidak dapat dikembalikan.';
        document.getElementById('modalDeleteAdmin').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    document.getElementById('modalDeleteAdmin').addEventListener('click', function(e) {
        if (e.target === this) { this.classList.remove('active'); document.body.style.overflow = ''; }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['modalDeleteAdmin','modalCheckin','modalCheckout','modalViewCondition'].forEach(closeModal);
        }
    });

    // ---- Filter Tab (tidak berubah) ----
    const tabs = document.querySelectorAll('.filter-tab');
    const rows = document.querySelectorAll('#bookingTableBody tr[data-period]');
    const noResult = document.getElementById('noBookingResult');
    const noText = document.getElementById('noBookingText');
    const tableWrapper = document.getElementById('tableWrapper');
    let activePeriodFilter = 'all';
    let activeDateFilter = 'all';

    const emptyLabels = {
        today: 'Tidak ada booking hari ini.',
        upcoming: 'Tidak ada booking yang akan datang.',
        past: 'Tidak ada riwayat booking.',
        all: 'Belum ada data booking.',
    };

    function applyTab(filter) { activePeriodFilter = filter; applyFilters(); }

    function applyFilters() {
        let count = 0;
        const today = new Date();
        rows.forEach(row => {
            const rowDate = new Date(row.dataset.date);
            let periodMatch = activePeriodFilter === 'all' || row.dataset.period === activePeriodFilter;
            let dateMatch = true;
            switch (activeDateFilter) {
                case 'today': dateMatch = rowDate.toDateString() === today.toDateString(); break;
                case 'month': dateMatch = rowDate.getMonth() === today.getMonth() && rowDate.getFullYear() === today.getFullYear(); break;
                case 'lastMonth':
                    const lastMonth = new Date(today); lastMonth.setMonth(lastMonth.getMonth() - 1);
                    dateMatch = rowDate.getMonth() === lastMonth.getMonth() && rowDate.getFullYear() === lastMonth.getFullYear();
                    break;
                case '3month':
                    const threeMonth = new Date(today); threeMonth.setMonth(today.getMonth() - 3);
                    dateMatch = rowDate >= threeMonth && rowDate <= today;
                    break;
                case 'year': dateMatch = rowDate.getFullYear() === today.getFullYear(); break;
            }
            if (periodMatch && dateMatch) { row.style.display = ''; count++; } else { row.style.display = 'none'; }
        });
        tableWrapper.style.display = count ? '' : 'none';
        noResult.style.display = count ? 'none' : 'block';
        noText.textContent = emptyLabels[activePeriodFilter];
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            applyTab(this.dataset.filter);
        });
    });
    applyTab('all');

    function toggleBookingFilter() { document.getElementById('bookingFilterDropdown').classList.toggle('show'); }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.booking-date-filter')) document.getElementById('bookingFilterDropdown').classList.remove('show');
    });
    function applyDateFilter(type) {
        activeDateFilter = type;
        const labels = { all: 'Filter', today: 'Hari Ini', month: 'Bulan Ini', lastMonth: 'Bulan Lalu', '3month': '3 Bulan Terakhir', year: 'Tahun Ini' };
        document.getElementById('bookingFilterLabel').innerHTML = labels[type];
        document.getElementById('bookingFilterDropdown').classList.remove('show');
        applyFilters();
    }
</script>
@endpush

@endsection