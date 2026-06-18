@extends('layouts.admin')

@section('title', 'Data Booking')

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
    $today    = \Carbon\Carbon::today();
    $cntToday    = $bookings->filter(fn($b) => \Carbon\Carbon::parse($b->date)->isToday())->count();
    $cntUpcoming = $bookings->filter(fn($b) => \Carbon\Carbon::parse($b->date)->startOfDay()->gt($today))->count();
    $cntPast     = $bookings->filter(fn($b) => \Carbon\Carbon::parse($b->date)->startOfDay()->lt($today))->count();
    $cntAll      = $bookings->count();
@endphp

{{-- Filter Tabs --}}
<div class="booking-filter-tabs">
    <button class="filter-tab active" data-filter="today">
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
    <button class="filter-tab" data-filter="all">
        <i class="bi bi-calendar3"></i>
        Semua
        <span class="tab-count">{{ $cntAll }}</span>
    </button>
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
                <tr data-period="{{ $period }}">
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

                    {{-- Status: wrapper berwarna + dropdown di dalamnya --}}
                    <td>
                        <form method="POST"
                              action="/admin/booking/update-status/{{ $booking->id }}"
                              class="status-form">
                            @csrf
                            <div class="status-wrapper status-{{ $booking->status }}" id="wrapper-{{ $booking->id }}">
                                <select name="status"
                                        onchange="updateStatusColor(this); this.form.submit()">
                                    <option value="pending"   {{ $booking->status=='pending'   ?'selected':'' }}>Pending</option>
                                    <option value="approved"  {{ $booking->status=='approved'  ?'selected':'' }}>Approved</option>
                                    <option value="rejected"  {{ $booking->status=='rejected'  ?'selected':'' }}>Rejected</option>
                                    <option value="ongoing"   {{ $booking->status=='ongoing'   ?'selected':'' }}>On Going</option>
                                    <option value="completed" {{ $booking->status=='completed' ?'selected':'' }}>Completed</option>
                                    <option value="cancelled" {{ $booking->status=='cancelled' ?'selected':'' }}>Cancelled</option>
                                </select>
                            </div>
                        </form>
                    </td>

                    {{-- Hapus --}}
                    <td>
                        <form action="{{ route('booking.destroy', $booking->id) }}"
                              method="POST" style="display:inline;"
                              onsubmit="return confirm('Yakin ingin menghapus booking ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-icon-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
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

    {{-- Empty state filter --}}
    <div id="noBookingResult" style="display:none; text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
        <i class="bi bi-calendar-x" style="font-size:32px; display:block; margin-bottom:8px;"></i>
        <span id="noBookingText">Tidak ada booking pada periode ini.</span>
    </div>
</div>


@push('scripts')
<script>
    // ---- Update warna wrapper saat dropdown berubah ----
    function updateStatusColor(select) {
        const wrapper = select.closest('.status-wrapper');
        const statuses = ['pending','approved','rejected','ongoing','completed','cancelled'];
        statuses.forEach(s => wrapper.classList.remove('status-' + s));
        wrapper.classList.add('status-' + select.value);
    }

    // ---- Filter Tab ----
    const tabs         = document.querySelectorAll('.filter-tab');
    const rows         = document.querySelectorAll('#bookingTableBody tr[data-period]');
    const noResult     = document.getElementById('noBookingResult');
    const noText       = document.getElementById('noBookingText');
    const tableWrapper = document.getElementById('tableWrapper');

    const emptyLabels = {
        today:    'Tidak ada booking hari ini.',
        upcoming: 'Tidak ada booking yang akan datang.',
        past:     'Tidak ada riwayat booking.',
        all:      'Belum ada data booking.',
    };

    function applyTab(filter) {
        let count = 0;
        rows.forEach(row => {
            const show = filter === 'all' || row.dataset.period === filter;
            row.style.display = show ? '' : 'none';
            if (show) count++;
        });

        tableWrapper.style.display = count === 0 ? 'none'  : '';
        noResult.style.display     = count === 0 ? 'block' : 'none';
        noText.textContent         = emptyLabels[filter] || emptyLabels.all;
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            applyTab(this.dataset.filter);
        });
    });

    applyTab('today');
</script>
@endpush

@endsection