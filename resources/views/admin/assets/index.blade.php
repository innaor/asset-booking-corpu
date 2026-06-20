@extends('layouts.admin')

@section('title', 'Data Aset')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Data Aset</h1>
        <p class="page-subtitle">Kelola aset yang tersedia untuk dipinjam</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-primary" id="btnOpenAssetModal">
            <i class="bi bi-plus-circle"></i>
            Tambah Aset
        </button>
        <button class="btn btn-outline-primary" id="btnOpenSubModal">
            <i class="bi bi-diagram-3"></i>
            Tambah Jenis
        </button>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Table --}}
<div class="card" style="padding:0; overflow:hidden;">
    <div class="table-wrapper" style="border:none; border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Nama Aset</th>
                    <th>Kategori</th>
                    <th>Jenis Aset</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                <tr>
                    <td style="font-weight:500;">{{ $asset->name }}</td>
                    <td>{{ $asset->subcategory->category->category_name }}</td>
                    <td>{{ $asset->subcategory->subcategory_name }}</td>
                    <td>
                        @if($asset->status == 'published')
                            <span class="badge badge-success">Published</span>
                        @else
                            <span class="badge badge-draft">Draft</span>
                        @endif
                    </td>
                    <td>
                        @if($asset->status == 'draft')
                            <a href="/admin/assets/toggle/{{ $asset->id }}"
                               class="btn btn-success btn-sm">
                                <i class="bi bi-eye"></i>
                                Publish
                            </a>
                        @else
                            <a href="/admin/assets/toggle/{{ $asset->id }}"
                               class="btn btn-secondary btn-sm">
                                <i class="bi bi-eye-slash"></i>
                                Unpublish
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:var(--space-xl); color:var(--color-gray-400);">
                        <i class="bi bi-box-seam" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        Belum ada aset. Tambahkan aset pertama Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ===================================================
     MODAL: TAMBAH ASET
     =================================================== --}}
<div class="modal-overlay" id="modalAset" role="dialog" aria-modal="true" aria-labelledby="modalAsetTitle">
    <div class="modal-box">

        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="modalAsetTitle">
                    <i class="bi bi-box-seam" style="color:var(--color-primary-400);"></i>
                    Tambah Aset
                </h2>
                <p class="modal-subtitle">Isi data aset yang ingin ditambahkan</p>
            </div>
            <button class="modal-close" id="btnCloseAssetModal" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @if($errors->hasAny(['name', 'subcategory_id']))
            <div class="alert alert-danger" style="margin-bottom:var(--space-md);">
                <div style="display:flex; align-items:flex-start; gap:8px;">
                    <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p style="margin:0 0 4px;">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="/admin/assets/store">
            @csrf

            <div class="form-group">
                <label for="name">Nama Aset</label>
                <input type="text" name="name" id="name"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Ruang Studio A"
                       required>
            </div>

            <div class="form-group">
                <label for="category_aset">Kategori</label>
                <select id="category_aset" required>
                    <option value="">— Pilih Kategori —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="subcategory_aset">Jenis</label>
                <select name="subcategory_id" id="subcategory_aset" required>
                    <option value="">— Pilih Jenis Aset —</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Simpan Aset
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelAssetModal">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </button>
            </div>

        </form>
    </div>
</div>


{{-- ===================================================
     MODAL: TAMBAH SUBKATEGORI
     =================================================== --}}
<div class="modal-overlay" id="modalSub" role="dialog" aria-modal="true" aria-labelledby="modalSubTitle">
    <div class="modal-box" style="max-width:440px;">

        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="modalSubTitle">
                    <i class="bi bi-diagram-3" style="color:var(--color-primary-400);"></i>
                    Tambah Jenis Aset
                </h2>
                <p class="modal-subtitle">Tambahkan Jenis Aset untuk pengelompokan aset</p>
            </div>
            <button class="modal-close" id="btnCloseSubModal" aria-label="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="/admin/subcategories/store">
            @csrf

            <div class="form-group">
                <label for="category_sub">Kategori</label>
                <select name="category_id" id="category_sub" required>
                    <option value="">— Pilih Kategori —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="subcategory_name">Jenis Aset</label>
                <input type="text" name="subcategory_name" id="subcategory_name"
                       placeholder="Contoh: Laptop"
                       required>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Simpan Jenis Aset
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelSubModal">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </button>
            </div>

        </form>
    </div>
</div>


@push('scripts')
<script>
    // ---- Helper open/close ----
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    }

    // ---- Modal Aset ----
    document.getElementById('btnOpenAssetModal').addEventListener('click',  () => openModal('modalAset'));
    document.getElementById('btnCloseAssetModal').addEventListener('click', () => closeModal('modalAset'));
    document.getElementById('btnCancelAssetModal').addEventListener('click',() => closeModal('modalAset'));
    document.getElementById('modalAset').addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalAset');
    });

    // ---- Modal Subkategori ----
    document.getElementById('btnOpenSubModal').addEventListener('click',  () => openModal('modalSub'));
    document.getElementById('btnCloseSubModal').addEventListener('click', () => closeModal('modalSub'));
    document.getElementById('btnCancelSubModal').addEventListener('click',() => closeModal('modalSub'));
    document.getElementById('modalSub').addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalSub');
    });

    // ---- ESC menutup semua modal ----
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('modalAset');
            closeModal('modalSub');
        }
    });

    // ---- Buka modal otomatis jika ada error ----
    @if($errors->any())
        openModal('modalAset');
    @endif

    // ---- Dropdown subkategori dinamis ----
    const subcategories = @json($subcategories);

    document.getElementById('category_aset').addEventListener('change', function() {
        const categoryId = this.value;
        const dropdown   = document.getElementById('subcategory_aset');
        dropdown.innerHTML = '<option value="">— Pilih Subkategori —</option>';
        subcategories.forEach(function(sub) {
            if (sub.category_id == categoryId) {
                dropdown.innerHTML += `<option value="${sub.id}">${sub.subcategory_name}</option>`;
            }
        });
    });
</script>
@endpush

@endsection