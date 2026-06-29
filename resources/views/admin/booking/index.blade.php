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
            <i class="bi bi-calendar3"></i>
            Semua
            <span class="tab-count">{{ $cntAll }}</span>
        </button>
        <button class="filter-tab" data-filter="today">
            <i class="bi bi-calendar-day"></i>
            Hari Ini
            <span class="tab-count">{{ $cntToday }}</span>
        </button>
        <button class="filter-tab" data-filter="upcoming">
            <i class="bi bi-calendar-plus"></i>
            Akan Datang
            <span class="tab-count">{{ $cntUpcoming }}</span>
        </button>
        <button class="filter-tab" data-filter="past">
            <i class="bi bi-calendar-minus"></i>
            Riwayat
            <span class="tab-count">{{ $cntPast }}</span>
        </button>
    </div>

    <div class="booking-date-filter">
        <button
            type="button"
            class="booking-filter-btn"
            onclick="toggleBookingFilter()">
            <i class="bi bi-calendar3"></i>
            <span id="bookingFilterLabel">
                Filter
            </span>
            <i class="bi bi-chevron-down"></i>
        </button>

        <div
            class="booking-filter-dropdown"
            id="bookingFilterDropdown">

            <button onclick="applyDateFilter('all')">
                Semua Waktu
            </button>

            <button onclick="applyDateFilter('today')">
                Hari Ini
            </button>

            <button onclick="applyDateFilter('month')">
                Bulan Ini
            </button>

            <button onclick="applyDateFilter('lastMonth')">
                Bulan Lalu
            </button>

            <button onclick="applyDateFilter('3month')">
                3 Bulan Terakhir
            </button>

            <button onclick="applyDateFilter('year')">
                Tahun Ini
            </button>

            <hr>

            <button onclick="openCustomDateModal()">
                Custom...
            </button>

        </div>

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
                <tr data-period="{{ $period }}"
                    data-date="{{ $booking->date }}">
                    <td style="font-weight:500;">
                        {{ $booking->user->name ?? $booking->guest_name }}
                    </td>
                    <td style="color:var(--color-gray-600);">
                        {{ $booking->user->phone ?? $booking->guest_phone }}
                    </td>
                    <td>{{ $booking->asset->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }}</td>
                    <td>
                        <span style="font-family:monospace; font-size:13px;">
                            {{ date('H:i', strtotime($booking->start_time)) }}
                            –
                            {{ date('H:i', strtotime($booking->end_time)) }}
                        </span>
                    </td>

                    {{-- Status wrapper berwarna --}}
                    <td>

                        <form method="POST"
                            action="/admin/booking/update-status/{{ $booking->id }}">

                            @csrf

                            <div
                                class="booking-status booking-status-{{ $booking->status }}"
                                id="wrapper-{{ $booking->id }}">

                                <select
                                    class="booking-status-select"
                                    name="status"
                                    onchange="updateStatusColor(this); this.form.submit();">

                                    <option value="pending"
                                        {{ $booking->status=='pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>

                                    <option value="approved"
                                        {{ $booking->status=='approved' ? 'selected' : '' }}>
                                        Approved
                                    </option>

                                    <option value="rejected"
                                        {{ $booking->status=='rejected' ? 'selected' : '' }}>
                                        Rejected
                                    </option>

                                    <option value="ongoing"
                                        {{ $booking->status=='ongoing' ? 'selected' : '' }}>
                                        On Going
                                    </option>

                                    <option value="completed"
                                        {{ $booking->status=='completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>

                                    <option value="cancelled"
                                        {{ $booking->status=='cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>

                                </select>

                            </div>

                        </form>

                    </td>

                    {{-- Hapus --}}
                    <td>
                        <button class="btn-icon btn-icon-danger"
                                title="Hapus booking"
                                onclick="confirmDeleteAdmin(
                                    '{{ route('booking.destroy', $booking->id) }}',
                                    '{{ $booking->user->name ?? $booking->guest_name }}',
                                    '{{ $booking->asset->name }}',
                                    '{{ \Carbon\Carbon::parse($booking->date)->format('d/m/Y') }}'
                                )">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
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
     MODAL: KONFIRMASI HAPUS (ADMIN)
     =================================================== --}}
<div class="modal-overlay" id="modalDeleteAdmin" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:420px; text-align:center; padding:var(--space-xl);">

        <div style="margin-bottom:var(--space-md);">
            <i class="bi bi-trash-fill" style="font-size:56px; color:var(--color-danger-btn);"></i>
        </div>

        <h2 style="font-size:18px; font-weight:600; color:var(--color-gray-900); margin-bottom:8px;">
            Hapus Booking?
        </h2>
        <p id="deleteAdminInfo" style="color:var(--color-gray-600); font-size:13px; margin-bottom:var(--space-lg); line-height:1.6;">
            Data booking ini akan dihapus permanen.
        </p>

        <form id="formDeleteAdmin" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="display:flex; gap:var(--space-sm);">
                <button type="button"
                        class="btn btn-secondary"
                        style="flex:1; justify-content:center;"
                        onclick="document.getElementById('modalDeleteAdmin').classList.remove('active'); document.body.style.overflow='';">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </button>
                <button type="submit"
                        class="btn btn-danger"
                        style="flex:1; justify-content:center;">
                    <i class="bi bi-trash"></i>
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>


@push('scripts')
<script>
    // ---- Update warna wrapper saat dropdown berubah ----
    function updateStatusColor(select)
    {
        const wrapper = select.closest('.booking-status');

        const statuses = [
            'pending',
            'approved',
            'rejected',
            'ongoing',
            'completed',
            'cancelled'
        ];

        statuses.forEach(status => {
            wrapper.classList.remove('booking-status-' + status);
        });

        wrapper.classList.add('booking-status-' + select.value);
    }

    // ---- Modal Hapus Admin ----
    function confirmDeleteAdmin(action, nama, aset, tanggal) {
        document.getElementById('formDeleteAdmin').action = action;
        document.getElementById('deleteAdminInfo').innerHTML =
            'Booking <strong>' + aset + '</strong> atas nama <strong>' + nama + '</strong>' +
            ' pada <strong>' + tanggal + '</strong> akan dihapus permanen dan tidak dapat dikembalikan.';
        document.getElementById('modalDeleteAdmin').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    document.getElementById('modalDeleteAdmin').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('modalDeleteAdmin').classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // ---- Filter Tab ----
    const tabs         = document.querySelectorAll('.filter-tab');
    const rows         = document.querySelectorAll('#bookingTableBody tr[data-period]');
    const noResult     = document.getElementById('noBookingResult');
    const noText       = document.getElementById('noBookingText');
    const tableWrapper = document.getElementById('tableWrapper');
    let activePeriodFilter = 'all';
    let activeDateFilter   = 'all';

    const emptyLabels = {
        today:    'Tidak ada booking hari ini.',
        upcoming: 'Tidak ada booking yang akan datang.',
        past:     'Tidak ada riwayat booking.',
        all:      'Belum ada data booking.',
    };

    function applyTab(filter) {
        activePeriodFilter = filter;
        applyFilters();
    }

    function applyFilters(){
        let count = 0;

        const today = new Date();

        rows.forEach(row => {

            const rowDate = new Date(row.dataset.date);

            //------------------------------------------------
            // FILTER TAB
            //------------------------------------------------

            let periodMatch =
                activePeriodFilter === 'all' ||
                row.dataset.period === activePeriodFilter;

            //------------------------------------------------
            // FILTER TANGGAL
            //------------------------------------------------

            let dateMatch = true;

            switch(activeDateFilter)
            {

                case 'today':

                    dateMatch =
                        rowDate.toDateString() === today.toDateString();

                    break;

                case 'month':

                    dateMatch =
                        rowDate.getMonth() === today.getMonth() &&
                        rowDate.getFullYear() === today.getFullYear();

                    break;

                case 'lastMonth':

                    const lastMonth = new Date(today);

                    lastMonth.setMonth(lastMonth.getMonth() - 1);

                    dateMatch =
                        rowDate.getMonth() === lastMonth.getMonth() &&
                        rowDate.getFullYear() === lastMonth.getFullYear();

                    break;

                case '3month':

                    const threeMonth = new Date(today);

                    threeMonth.setMonth(today.getMonth() - 3);

                    dateMatch =
                        rowDate >= threeMonth &&
                        rowDate <= today;

                    break;

                case 'year':

                    dateMatch =
                        rowDate.getFullYear() === today.getFullYear();

                    break;

            }

            //------------------------------------------------

            if(periodMatch && dateMatch)
            {
                row.style.display = '';
                count++;
            }
            else
            {
                row.style.display = 'none';
            }

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


function toggleBookingFilter()
{
    document
        .getElementById('bookingFilterDropdown')
        .classList.toggle('show');
}

document.addEventListener('click', function(e){

    if(!e.target.closest('.booking-date-filter'))
    {
        document
            .getElementById('bookingFilterDropdown')
            .classList.remove('show');
    }

});


function applyDateFilter(type){
    activeDateFilter = type;

    const labels = {

        all : 'Filter',

        today : 'Hari Ini',

        month : 'Bulan Ini',

        lastMonth : 'Bulan Lalu',

        '3month' : '3 Bulan Terakhir',

        year : 'Tahun Ini'

    };

    document.getElementById('bookingFilterLabel').innerHTML =
        labels[type];

    document
        .getElementById('bookingFilterDropdown')
        .classList.remove('show');

    applyFilters();
}
</script>
@endpush

@endsection