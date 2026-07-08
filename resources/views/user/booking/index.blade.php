@extends('layouts.user')

@section('title', 'Booking Saya')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Booking Saya</h1>
        <p class="page-subtitle">Riwayat dan status peminjaman aset Anda</p>
    </div>
    <button class="btn btn-primary" id="btnOpenModal">
        <i class="bi bi-plus-circle"></i>
        Booking Baru
    </button>
</div>

{{-- Table Card --}}
<div class="card" style="padding:0; overflow:hidden;">
    <div class="table-wrapper" style="border:none; border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Nama Aset</th>
                    <th>Tanggal</th>
                    <th>Jam Peminjaman</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $item)
                <tr>
                    <td style="font-weight:500;">{{ $item->asset->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                    <td>
                        <span style="font-family:monospace; font-size:13px;">
                            {{ date('H:i', strtotime($item->start_time)) }}
                            –
                            {{ date('H:i', strtotime($item->end_time)) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $badgeClass = match($item->status) {
                                'pending'   => 'badge-pending',
                                'approved'  => 'badge-approved',
                                'ongoing'   => 'badge-ongoing',
                                'completed' => 'badge-completed',
                                'rejected'  => 'badge-danger',
                                'cancelled' => 'badge-danger',
                                default     => 'badge-draft',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td>
                        <div class="btn-group">
                            @if($item->status == 'pending')

                                {{-- Tombol Edit --}}
                                <button class="btn-icon btn-icon-primary"
                                        title="Edit booking"
                                        onclick="openEditModal(
                                            {{ $item->id }},
                                            '{{ $item->asset_id }}',
                                            '{{ $item->date }}',
                                            '{{ substr($item->start_time, 0, 8) }}',
                                            '{{ substr($item->end_time, 0, 8) }}'
                                        )">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                {{-- Tombol Hapus --}}
                                <button class="btn-icon btn-icon-danger"
                                        title="Hapus booking"
                                        onclick="confirmDelete(
                                            '{{ $item->id }}',
                                            '{{ $item->asset->name }}',
                                            '{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}'
                                        )">
                                    <i class="bi bi-trash"></i>
                                </button>

                            @else

                                <button class="btn-icon"
                                        disabled
                                        title="Booking hanya dapat diedit saat masih Pending"
                                        style="opacity:.4; cursor:not-allowed;">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <button class="btn-icon"
                                        disabled
                                        title="Booking hanya dapat dihapus saat masih Pending"
                                        style="opacity:.4; cursor:not-allowed;">
                                    <i class="bi bi-trash"></i>
                                </button>

                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
                        <i class="bi bi-calendar-x" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        Belum ada booking.
                        <a href="#" onclick="document.getElementById('btnOpenModal').click(); return false;">
                            Buat booking sekarang
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ===================================================
     MODAL: BUAT BOOKING BARU
     =================================================== --}}
<div class="modal-overlay" id="modalBooking" role="dialog" aria-modal="true">
    <div class="modal-box">

        <div class="modal-header">
            <div>
                <h2 class="modal-title">
                    <i class="bi bi-calendar2-plus" style="color:var(--color-primary-400);"></i>
                    Buat Booking Baru
                </h2>
                <p class="modal-subtitle">Isi form di bawah untuk mengajukan peminjaman aset</p>
            </div>
            <button class="modal-close" id="btnCloseModal" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @if ($errors->any() && !session('_edit_mode'))
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

        <form method="POST" action="/user/booking/store" id="formBooking">
            @csrf
            <div class="form-group">
                <label>Pilih Aset</label>
                <select name="asset_id" id="new_asset_id" required>
                    <option value="">— Pilih Aset —</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                            {{ $asset->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Peminjaman</label>
                <input type="date" name="date" id="new_date"
                       value="{{ old('date') }}" min="{{ date('Y-m-d') }}" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <select name="start_time" id="start_time" required>
                        <option value="" selected>-</option>
                        @for($i = 8; $i <= 18; $i++)
                            <option value="{{ sprintf('%02d:00', $i) }}">
                                {{ sprintf('%02d:00', $i) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Selesai</label>
                    <select name="end_time" id="end_time" required>
                        <option value="" selected>-</option>
                        @for($i = 9; $i <= 19; $i++)
                            <option value="{{ sprintf('%02d:00', $i) }}">
                                {{ sprintf('%02d:00', $i) }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Booking
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelModal">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: EDIT BOOKING
     =================================================== --}}
<div class="modal-overlay" id="modalEdit" role="dialog" aria-modal="true">
    <div class="modal-box">

        <div class="modal-header">
            <div>
                <h2 class="modal-title">
                    <i class="bi bi-pencil-square" style="color:var(--color-primary-400);"></i>
                    Edit Booking
                </h2>
                <p class="modal-subtitle">Ubah detail booking Anda di bawah ini</p>
            </div>
            <button class="modal-close" id="btnCloseEdit" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @if ($errors->any() && session('_edit_mode'))
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

        <form method="POST" id="formEdit" action="">
            @csrf
            <div class="form-group">
                <label>Pilih Aset</label>
                <select name="asset_id" id="edit_asset_id" required>
                    <option value="">— Pilih Aset —</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Peminjaman</label>
                <input type="date" name="date" id="edit_date"
                       min="{{ date('Y-m-d') }}" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md);">
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <select name="start_time" id="edit_start_time" required>
                        <option value="" selected>-</option>
                        @for($i = 8; $i <= 16; $i++)
                            <option value="{{ sprintf('%02d:00:00', $i) }}">
                                {{ sprintf('%02d:00', $i) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Selesai</label>
                    <select name="end_time" id="edit_end_time" required>
                        <option value="" selected>-</option>
                        @for($i = 9; $i <= 17; $i++)
                            <option value="{{ sprintf('%02d:00:00', $i) }}">
                                {{ sprintf('%02d:00', $i) }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelEdit">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: KONFIRMASI HAPUS
     =================================================== --}}
<div class="modal-overlay" id="modalDelete" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:420px; text-align:center; padding:var(--space-xl);">

        <div style="margin-bottom:var(--space-md);">
            <i class="bi bi-trash-fill" style="font-size:56px; color:var(--color-danger-btn);"></i>
        </div>

        <h2 style="font-size:18px; font-weight:600; color:var(--color-gray-900); margin-bottom:8px;">
            Hapus Booking?
        </h2>
        <p id="deleteInfo" style="color:var(--color-gray-600); font-size:13px; margin-bottom:var(--space-lg);">
            Booking ini akan dihapus permanen.
        </p>

        <form id="formDelete" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="display:flex; gap:var(--space-sm);">
                <button type="button" class="btn btn-secondary"
                        style="flex:1; justify-content:center;"
                        onclick="closeModal('modalDelete')">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
                <button type="submit" class="btn btn-danger"
                        style="flex:1; justify-content:center;">
                    <i class="bi bi-trash"></i> Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: BERHASIL DIHAPUS
     =================================================== --}}
@if(session('delete_success'))
<div class="modal-overlay active" id="modalDeleteSuccess" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:400px; text-align:center; padding:var(--space-xl);">
        <i class="bi bi-trash-fill"
           style="font-size:56px; color:var(--color-danger-btn); display:block; margin-bottom:var(--space-md);"></i>
        <h2 style="font-size:20px; font-weight:600; color:var(--color-gray-900); margin-bottom:8px;">
            Booking Dihapus
        </h2>
        <p style="color:var(--color-gray-600); font-size:14px; margin-bottom:var(--space-lg);">
            {{ session('delete_success') }}
        </p>
        <button class="btn btn-secondary" style="justify-content:center; width:100%;"
                onclick="closeModal('modalDeleteSuccess')">
            <i class="bi bi-check"></i> Oke
        </button>
    </div>
</div>
@endif


{{-- ===================================================
     MODAL: BERHASIL EDIT
     =================================================== --}}
@if(session('edit_success'))
<div class="modal-overlay active" id="modalEditSuccess" role="dialog" aria-modal="true">
    <div class="modal-box" style="max-width:400px; text-align:center; padding:var(--space-xl);">
        <i class="bi bi-check-circle-fill"
           style="font-size:64px; color:var(--color-success-btn); display:block; margin-bottom:var(--space-md);"></i>
        <h2 style="font-size:20px; font-weight:600; color:var(--color-gray-900); margin-bottom:8px;">
            Perubahan Disimpan!
        </h2>
        <p style="color:var(--color-gray-600); font-size:14px; margin-bottom:var(--space-lg);">
            {{ session('edit_success') }}
        </p>
        <button class="btn btn-success" style="justify-content:center; width:100%;"
                onclick="closeModal('modalEditSuccess')">
            <i class="bi bi-check"></i> Oke, Mengerti
        </button>
    </div>
</div>
@endif


@push('scripts')
<script>
    // ============================================================
    // HELPER
    // ============================================================
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    }

    // ============================================================
    // MODAL BOOKING BARU
    // ============================================================
    const nowHour  = {{ date('G') }};
    const todayStr = '{{ date('Y-m-d') }}';

    function updateDisabledHours(dateEl, startEl, endEl) {

        const isToday = dateEl.value === todayStr;

        // Simpan semua option sekali saja
        if (!startEl.dataset.original) {
            startEl.dataset.original = startEl.innerHTML;
        }

        if (!endEl.dataset.original) {
            endEl.dataset.original = endEl.innerHTML;
        }

        // Reset dropdown
        startEl.innerHTML = startEl.dataset.original;
        endEl.innerHTML   = endEl.dataset.original;

        // ==========================
        // FILTER JAM MULAI
        // ==========================
        Array.from(startEl.options).forEach(opt => {

            const h = parseInt(opt.value);

            if (isNaN(h)) return;

            if (isToday && h <= nowHour) {
                opt.remove();
            }

        });

        // ==========================
        // FILTER JAM SELESAI
        // ==========================
        const startHour = parseInt(startEl.value);

        Array.from(endEl.options).forEach(opt => {

            const h = parseInt(opt.value);

            if (isNaN(h)) return;

            if (
                h <= startHour ||
                (isToday && h <= nowHour + 1)
            ) {
                opt.remove();
            }

        });

    }

    const btnOpen   = document.getElementById('btnOpenModal');
    const btnClose  = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancelModal');
    const newDate   = document.getElementById('new_date');
    const newStart  = document.getElementById('new_start_time');
    const newEnd    = document.getElementById('new_end_time');

    // btnOpen.addEventListener('click', () => {
    //     openModal('modalBooking');
    //     updateDisabledHours(newDate, newStart, newEnd);
    // });
    // btnClose.addEventListener('click',  () => closeModal('modalBooking'));
    // btnCancel.addEventListener('click', () => closeModal('modalBooking'));
    // document.getElementById('modalBooking').addEventListener('click', function(e) {
    //     if (e.target === this) closeModal('modalBooking');
    // });
    // newDate.addEventListener('change',  () => updateDisabledHours(newDate, newStart, newEnd));
    // newStart.addEventListener('change', () => updateDisabledHours(newDate, newStart, newEnd));

    console.log({
        btnOpen,
        btnClose,
        btnCancel,
        newDate,
        newStart,
        newEnd,
        modalBooking: document.getElementById('modalBooking')
    });

    btnOpen?.addEventListener('click', () => {
        openModal('modalBooking');
        updateDisabledHours(newDate, newStart, newEnd);
    });

    btnClose?.addEventListener('click', () => closeModal('modalBooking'));
    btnCancel?.addEventListener('click', () => closeModal('modalBooking'));

    document.getElementById('modalBooking')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalBooking');
    });

    newDate?.addEventListener('change', () => updateDisabledHours(newDate, newStart, newEnd));
    newStart?.addEventListener('change', () => updateDisabledHours(newDate, newStart, newEnd));

    @if($errors->any() && !session('_edit_mode'))
        openModal('modalBooking');
    @endif

    // ============================================================
    // MODAL EDIT
    // ============================================================
    const editDate  = document.getElementById('edit_date');
    const editStart = document.getElementById('edit_start_time');
    const editEnd   = document.getElementById('edit_end_time');

    function openEditModal(id, assetId, date, startTime, endTime) {
        console.log('EDIT DIKLIK');
        // Isi form
        document.getElementById('formEdit').action = '/user/booking/update/' + id;
        document.getElementById('edit_asset_id').value = assetId;
        document.getElementById('edit_date').value     = date;

        // Set jam mulai
        Array.from(editStart.options).forEach(opt => {
            opt.selected = opt.value === startTime;
        });
        // Set jam selesai
        Array.from(editEnd.options).forEach(opt => { 
            opt.selected = opt.value === endTime;
        });

        openModal('modalEdit');
        updateDisabledHours(editDate, editStart, editEnd);
    }

    // document.getElementById('btnCloseEdit').addEventListener('click',  () => closeModal('modalEdit'));
    // document.getElementById('btnCancelEdit').addEventListener('click', () => closeModal('modalEdit'));
    // document.getElementById('modalEdit').addEventListener('click', function(e) {
    //     if (e.target === this) closeModal('modalEdit');
    // });
    // editDate.addEventListener('change',  () => updateDisabledHours(editDate, editStart, editEnd));
    // editStart.addEventListener('change', () => updateDisabledHours(editDate, editStart, editEnd));

    console.log({
        editDate,
        editStart,
        editEnd,
        btnCloseEdit: document.getElementById('btnCloseEdit'),
        btnCancelEdit: document.getElementById('btnCancelEdit'),
        modalEdit: document.getElementById('modalEdit')
    });

    document.getElementById('btnCloseEdit')?.addEventListener('click', () => closeModal('modalEdit'));

    document.getElementById('btnCancelEdit')?.addEventListener('click', () => closeModal('modalEdit'));

    document.getElementById('modalEdit')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalEdit');
    });

    editDate?.addEventListener('change', () => updateDisabledHours(editDate, editStart, editEnd));

    editStart?.addEventListener('change', () => updateDisabledHours(editDate, editStart, editEnd));

    @if($errors->any() && session('_edit_mode'))
        openModal('modalEdit');
    @endif

    // ============================================================
    // MODAL HAPUS (konfirmasi)
    // ============================================================
    function confirmDelete(id, assetName, date) {
        document.getElementById('formDelete').action = '/user/booking/delete/' + id;
        document.getElementById('deleteInfo').textContent =
            'Booking ' + assetName + ' pada ' + date + ' akan dihapus permanen dan tidak dapat dikembalikan.';
        openModal('modalDelete');
    }

    document.getElementById('modalDelete').addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalDelete');
    });

    // ESC menutup semua modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['modalBooking','modalEdit','modalDelete'].forEach(closeModal);
        }
    });
</script>
@endpush

@endsection