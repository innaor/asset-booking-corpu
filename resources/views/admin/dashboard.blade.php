@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Monitoring penggunaan aset secara keseluruhan</p>
    </div>
</div>

{{-- Schedule Card --}}
<div class="card">

    {{-- Toolbar Filter --}}
    <div class="schedule-toolbar">

        {{-- Search aset --}}
        <div class="search-box">
            <i class="bi bi-search search-icon"></i>
            <input type="text"
                   id="searchAset"
                   class="search-input"
                   placeholder="Cari aset...">
        </div>

        {{-- Filter Kategori --}}
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

        {{-- Filter Tanggal --}}
        <div class="toolbar-right">
            <label class="date-label">
                <i class="bi bi-calendar3"></i>
                Tanggal
            </label>
            <input type="date"
                   id="filterTanggal"
                   class="filter-date"
                   value="{{ $date }}">
        </div>

    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="schedule-table" id="scheduleTable">
            <thead>
                <tr>
                    <th class="asset-name-col">Aset</th>
                    @for($hour = 8; $hour <= 16; $hour++)
                        <th>{{ sprintf('%02d:00', $hour) }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody id="scheduleBody">
            @foreach($assets as $asset)
            <tr data-asset-name="{{ strtolower($asset->name) }}"
                data-category-id="{{ $asset->subcategory->category_id ?? '' }}">

                <td class="asset-name-col">{{ $asset->name }}</td>

                @php
                    $skipUntil = []; // jam yang harus di-skip karena sudah di-colspan
                @endphp

                @for($hour = 8; $hour <= 16; $hour++)
                    @php
                        // Kalau jam ini di-skip, lewati
                        if (in_array($hour, $skipUntil)) continue;

                        // Cari booking yang mulai tepat di jam ini
                        $booking = $bookings->first(function($b) use ($asset, $hour) {
                            $start = (int) date('H', strtotime($b->start_time));
                            return $b->asset_id == $asset->id && $start === $hour;
                        });

                        if ($booking) {
                            $start    = (int) date('H', strtotime($booking->start_time));
                            $end      = (int) date('H', strtotime($booking->end_time));
                            $duration = $end - $start; // jumlah kolom yang dicakup
                            $colspan  = min($duration, 17 - $hour); // tidak melebihi batas tabel

                            // Tandai jam-jam berikutnya sebagai skip
                            for ($s = $hour + 1; $s < $hour + $colspan; $s++) {
                                $skipUntil[] = $s;
                            }

                            $slotClass = 'time-slot slot-booked';
                            $slotClass .= match($booking->status) {
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

                    <td class="{{ $slotClass }}"
                        @if($colspan > 1) colspan="{{ $colspan }}" @endif>
                        @if($booking)
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

@push('scripts')
<script>
    // ---- Tanggal: auto reload ke server ----
    document.getElementById('filterTanggal').addEventListener('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('date', this.value);
        window.location.href = url.toString();
    });

    // ---- Search + Kategori: realtime JS ----
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

            if (matchSearch && matchKategori) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        tableWrapper.style.display = visibleCount === 0 ? 'none'  : '';
        noResult.style.display     = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', applyFilters);
    filterKategori.addEventListener('change', applyFilters);
</script>
@endpush

@endsection