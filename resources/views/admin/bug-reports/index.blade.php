@extends('layouts.admin')

@section('title', 'Aduan Bug')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/bug-reports.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Aduan Bug</h1>
        <p class="page-subtitle">Kelola dan tindak lanjuti aduan bug dari pengguna</p>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Filter Tabs --}}
<div class="bug-filter-tabs">
    <a href="/admin/bug-reports?status=all" class="bug-filter-tab {{ !request('status') || request('status') == 'all' ? 'active' : '' }}">
        <i class="bi bi-flag"></i> Semua <span class="bug-tab-count">{{ $counts['all'] }}</span>
    </a>
    <a href="/admin/bug-reports?status=pending" class="bug-filter-tab {{ request('status') == 'pending' ? 'active' : '' }}">
        <i class="bi bi-hourglass-split"></i> Menunggu <span class="bug-tab-count">{{ $counts['pending'] }}</span>
    </a>
    <a href="/admin/bug-reports?status=in_progress" class="bug-filter-tab {{ request('status') == 'in_progress' ? 'active' : '' }}">
        <i class="bi bi-arrow-repeat"></i> Diproses <span class="bug-tab-count">{{ $counts['in_progress'] }}</span>
    </a>
    <a href="/admin/bug-reports?status=resolved" class="bug-filter-tab {{ request('status') == 'resolved' ? 'active' : '' }}">
        <i class="bi bi-check-circle"></i> Selesai <span class="bug-tab-count">{{ $counts['resolved'] }}</span>
    </a>
    <a href="/admin/bug-reports?status=rejected" class="bug-filter-tab {{ request('status') == 'rejected' ? 'active' : '' }}">
        <i class="bi bi-x-circle"></i> Ditolak <span class="bug-tab-count">{{ $counts['rejected'] }}</span>
    </a>
</div>

{{-- Table --}}
<div class="card" style="padding:0; overflow:hidden;">
    <div class="table-wrapper" style="border:none; border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Pelapor</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tanggal Lapor</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bugReports as $item)
                @php
                    $categoryLabel = match($item->category) {
                        'ui'            => 'Tampilan/UI',
                        'data'          => 'Data Tidak Sesuai',
                        'system_error'  => 'Error Sistem',
                        default         => 'Lainnya',
                    };
                    $statusLabel = match($item->status) {
                        'pending'     => 'Menunggu',
                        'in_progress' => 'Diproses',
                        'resolved'    => 'Selesai',
                        'rejected'    => 'Ditolak',
                        default       => ucfirst($item->status),
                    };
                @endphp
                <tr>
                    <td style="font-weight:500;">{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $categoryLabel }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="bug-status-badge bug-status-{{ $item->status }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        <button class="btn-icon btn-icon-primary"
                                title="Tindak lanjuti"
                                onclick="openBugModal(
                                    {{ $item->id }},
                                    '{{ $item->title }}',
                                    '{{ $categoryLabel }}',
                                    '{{ $item->related_page ?? '-' }}',
                                    `{{ $item->description }}`,
                                    '{{ $item->attachment_path ? asset('storage/'.$item->attachment_path) : '' }}',
                                    '{{ $item->user->name ?? '-' }}',
                                    '{{ $item->status }}',
                                    `{{ $item->admin_note ?? '' }}`
                                )">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
                        <i class="bi bi-flag" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        Belum ada aduan bug.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ===================================================
     MODAL: DETAIL & TINDAK LANJUT ADUAN
     =================================================== --}}
<div class="modal-overlay" id="modalBug" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="bugTitle">
                    <i class="bi bi-flag" style="color:var(--color-primary-400);"></i>
                    Detail Aduan
                </h2>
                <p class="modal-subtitle" id="bugReporter"></p>
            </div>
            <button class="modal-close" onclick="closeModal('modalBug')" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <p id="bugCategory" style="margin:0;"></p>
        </div>
        <div class="form-group">
            <label>Halaman Terkait</label>
            <p id="bugPage" style="margin:0;"></p>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <p id="bugDescription" style="margin:0; white-space:pre-line;"></p>
        </div>
        <div class="form-group" id="bugAttachmentWrapper" style="display:none;">
            <label>Screenshot</label>
            <img id="bugAttachment" src="" style="max-width:100%; border-radius:8px; border:1px solid var(--color-gray-200);">
        </div>

        <form method="POST" id="formBugStatus" action="">
            @csrf
            <div class="form-group">
                <label>Ubah Status</label>
                <select name="status" id="bugStatusSelect" required onchange="toggleNoteRequired()">
                    <option value="pending">Menunggu</option>
                    <option value="in_progress">Diproses</option>
                    <option value="resolved">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
            <div class="form-group">
                <label id="bugNoteLabel">Catatan untuk Pengguna <span style="font-weight:400; color:var(--color-gray-400);">(opsional)</span></label>
                <textarea name="admin_note" id="bugNoteInput" rows="3" placeholder="Jelaskan tindak lanjut atau alasan penolakan...">{{ old('admin_note') }}</textarea>
                <small id="bugNoteHint" style="display:none; color:var(--color-danger-btn);">
                    Catatan wajib diisi saat status "Selesai" atau "Ditolak".
                </small>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalBug')">
                    <i class="bi bi-x-circle"></i> Tutup
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

    function openBugModal(id, title, category, page, description, attachment, reporter, status, note) {
        document.getElementById('bugTitle').innerHTML =
            '<i class="bi bi-flag" style="color:var(--color-primary-400);"></i> ' + title;
        document.getElementById('bugReporter').textContent = 'Dilaporkan oleh: ' + reporter;
        document.getElementById('bugCategory').textContent = category;
        document.getElementById('bugPage').textContent = page;
        document.getElementById('bugDescription').textContent = description;

        const attWrapper = document.getElementById('bugAttachmentWrapper');
        if (attachment) {
            document.getElementById('bugAttachment').src = attachment;
            attWrapper.style.display = 'block';
        } else {
            attWrapper.style.display = 'none';
        }

        document.getElementById('formBugStatus').action = '/admin/bug-reports/' + id + '/update-status';
        document.getElementById('bugStatusSelect').value = status;
        document.getElementById('bugNoteInput').value = note;

        toggleNoteRequired();
        openModal('modalBug');
    }

    function toggleNoteRequired() {
        const status = document.getElementById('bugStatusSelect').value;
        const noteInput = document.getElementById('bugNoteInput');
        const noteHint = document.getElementById('bugNoteHint');

        if (status === 'resolved' || status === 'rejected') {
            noteInput.required = true;
            noteHint.style.display = 'block';
        } else {
            noteInput.required = false;
            noteHint.style.display = 'none';
        }
    }

    document.getElementById('modalBug')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalBug');
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal('modalBug');
    });

    @if($errors->any())
        openModal('modalBug');
    @endif
</script>
@endpush

@endsection