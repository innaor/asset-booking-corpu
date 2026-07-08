@extends('layouts.user')

@section('title', 'Aduan Bug')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Aduan Bug</h1>
        <p class="page-subtitle">Laporkan kendala atau error yang Anda temui pada sistem</p>
    </div>
    <button class="btn btn-primary" id="btnOpenModal">
        <i class="bi bi-flag"></i>
        Lapor Bug
    </button>
</div>

{{-- Table Card --}}
<div class="card" style="padding:0; overflow:hidden;">
    <div class="table-wrapper" style="border:none; border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tanggal Lapor</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bugReports as $item)
                <tr>
                    <td style="font-weight:500;">{{ $item->title }}</td>
                    <td>
                        @php
                            $categoryLabel = match($item->category) {
                                'ui'            => 'Tampilan/UI',
                                'data'          => 'Data Tidak Sesuai',
                                'system_error'  => 'Error Sistem',
                                default         => 'Lainnya',
                            };
                        @endphp
                        {{ $categoryLabel }}
                    </td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @php
                            $badgeClass = match($item->status) {
                                'pending'     => 'badge-pending',
                                'in_progress' => 'badge-ongoing',
                                'resolved'    => 'badge-completed',
                                'rejected'    => 'badge-danger',
                                default       => 'badge-draft',
                            };
                            $statusLabel = match($item->status) {
                                'pending'     => 'Menunggu',
                                'in_progress' => 'Diproses',
                                'resolved'    => 'Selesai',
                                'rejected'    => 'Ditolak',
                                default       => ucfirst($item->status),
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        <button class="btn-icon btn-icon-primary"
                                title="Lihat detail"
                                onclick="openDetailModal(
                                    '{{ $item->title }}',
                                    '{{ $categoryLabel }}',
                                    '{{ $item->related_page ?? '-' }}',
                                    `{{ $item->description }}`,
                                    '{{ $item->attachment_path ? asset('storage/'.$item->attachment_path) : '' }}',
                                    '{{ $statusLabel }}',
                                    `{{ $item->admin_note ?? '' }}`
                                )">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
                        <i class="bi bi-flag" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        Belum ada aduan bug.
                        <a href="#" onclick="document.getElementById('btnOpenModal').click(); return false;">
                            Lapor bug sekarang
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ===================================================
     MODAL: LAPOR BUG BARU
     =================================================== --}}
<div class="modal-overlay" id="modalBugReport" role="dialog" aria-modal="true">
    <div class="modal-box">

        <div class="modal-header">
            <div>
                <h2 class="modal-title">
                    <i class="bi bi-flag" style="color:var(--color-primary-400);"></i>
                    Lapor Bug
                </h2>
                <p class="modal-subtitle">Jelaskan kendala yang Anda temui sedetail mungkin</p>
            </div>
            <button class="modal-close" id="btnCloseModal" aria-label="Tutup">
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

        <form method="POST" action="/user/bug-reports/store" id="formBugReport" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Judul Aduan</label>
                <input type="text" name="title" placeholder="Contoh: Gagal submit form booking"
                       value="{{ old('title') }}" maxlength="150" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category" required>
                    <option value="">— Pilih Kategori —</option>
                    <option value="ui" {{ old('category') == 'ui' ? 'selected' : '' }}>Tampilan/UI</option>
                    <option value="data" {{ old('category') == 'data' ? 'selected' : '' }}>Data Tidak Sesuai</option>
                    <option value="system_error" {{ old('category') == 'system_error' ? 'selected' : '' }}>Error Sistem</option>
                    <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label>Halaman Terkait <span style="font-weight:400; color:var(--color-gray-400);">(opsional)</span></label>
                <input type="text" name="related_page" placeholder="Contoh: Form Peminjaman Aset"
                       value="{{ old('related_page') }}" maxlength="100">
            </div>
            <div class="form-group">
                <label>Deskripsi Bug</label>
                <textarea name="description" rows="4" placeholder="Jelaskan apa yang terjadi..." required>{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label>Screenshot <span style="font-weight:400; color:var(--color-gray-400);">(opsional, maks 2MB)</span></label>
                <input type="file" name="attachment" accept="image/*">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Kirim Aduan
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelModal">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: DETAIL ADUAN
     =================================================== --}}
<div class="modal-overlay" id="modalDetail" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="detailTitle">
                    <i class="bi bi-flag" style="color:var(--color-primary-400);"></i>
                    Detail Aduan
                </h2>
                <p class="modal-subtitle" id="detailStatus"></p>
            </div>
            <button class="modal-close" onclick="closeModal('modalDetail')" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <p id="detailCategory" style="margin:0;"></p>
        </div>
        <div class="form-group">
            <label>Halaman Terkait</label>
            <p id="detailPage" style="margin:0;"></p>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <p id="detailDescription" style="margin:0; white-space:pre-line;"></p>
        </div>
        <div class="form-group" id="detailAttachmentWrapper" style="display:none;">
            <label>Screenshot</label>
            <img id="detailAttachment" src="" style="max-width:100%; border-radius:8px; border:1px solid var(--color-gray-200);">
        </div>
        <div class="form-group" id="detailNoteWrapper" style="display:none;">
            <label>Catatan Admin</label>
            <p id="detailNote" style="margin:0; white-space:pre-line; background:var(--color-gray-50); padding:12px; border-radius:8px;"></p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDetail')">
                <i class="bi bi-x-circle"></i> Tutup
            </button>
        </div>
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

    const btnOpen   = document.getElementById('btnOpenModal');
    const btnClose  = document.getElementById('btnCloseModal');
    const btnCancel = document.getElementById('btnCancelModal');

    btnOpen?.addEventListener('click', () => openModal('modalBugReport'));
    btnClose?.addEventListener('click', () => closeModal('modalBugReport'));
    btnCancel?.addEventListener('click', () => closeModal('modalBugReport'));

    document.getElementById('modalBugReport')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalBugReport');
    });

    @if($errors->any())
        openModal('modalBugReport');
    @endif

    function openDetailModal(title, category, page, description, attachment, status, note) {
        document.getElementById('detailTitle').innerHTML =
            '<i class="bi bi-flag" style="color:var(--color-primary-400);"></i> ' + title;
        document.getElementById('detailStatus').textContent = 'Status: ' + status;
        document.getElementById('detailCategory').textContent = category;
        document.getElementById('detailPage').textContent = page;
        document.getElementById('detailDescription').textContent = description;

        const attWrapper = document.getElementById('detailAttachmentWrapper');
        if (attachment) {
            document.getElementById('detailAttachment').src = attachment;
            attWrapper.style.display = 'block';
        } else {
            attWrapper.style.display = 'none';
        }

        const noteWrapper = document.getElementById('detailNoteWrapper');
        if (note) {
            document.getElementById('detailNote').textContent = note;
            noteWrapper.style.display = 'block';
        } else {
            noteWrapper.style.display = 'none';
        }

        openModal('modalDetail');
    }

    document.getElementById('modalDetail')?.addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalDetail');
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ['modalBugReport', 'modalDetail'].forEach(closeModal);
        }
    });
</script>
@endpush

@endsection