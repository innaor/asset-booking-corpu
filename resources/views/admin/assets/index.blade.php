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
                        <div class="btn-grup">

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

                            {{-- Edit --}}
                            <button
                                type="button"
                                class="btn-icon btn-icon-primary"
                                title="Edit Aset"
                                onclick="openEditAsset(
                                    {{ $asset->id }},
                                    '{{ $asset->name }}',
                                    {{ $asset->subcategory->category->id }},
                                    {{ $asset->subcategory_id }}
                                )">
                                <i class="bi bi-pencil"></i>
                            </button>

                            {{-- Delete --}}
                            <form action="{{ route('admin.assets.destroy', $asset->id) }}"
                                method="POST"
                                style="display:inline"
                                onsubmit="return confirm('Hapus aset {{ $asset->name }}?')">

                                @csrf
                                @method('DELETE')

                                <button
                                    type="button"
                                    class="btn-icon btn-icon-danger"
                                    title="Hapus Asset"
                                    onclick="confirmDeleteAsset(
                                        {{ $asset->id }},
                                        '{{ $asset->name }}'
                                    )">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        </div>
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
                        @if($errors->has('name'))
                            <p style="margin:0 0 4px;">{{ $errors->first('name') }}</p>
                        @endif
                        @if($errors->has('subcategory_id'))
                            <p style="margin:0 0 4px;">{{ $errors->first('subcategory_id') }}</p>
                        @endif
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
     MODAL: EDIT ASSET
     =================================================== --}}
<div id="editAssetModal" class="modal-overlay">
    <div class="modal-box">

        <div class="modal-header">
            <div>
                <h3 class="modal-title">
                    <i class="bi bi-pencil-square"></i>
                    Edit Asset
                </h3>
                <p class="modal-subtitle">
                    Ubah informasi asset.
                </p>
            </div>

            <button class="modal-close"
                    type="button"
                    onclick="closeModal('editAssetModal')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form
            id="formEditAsset"
            method="POST">

            @csrf

            <div class="form-group">
                <label>Nama Asset</label>

                <input
                    type="text"
                    name="name"
                    id="edit_name"
                    required>
            </div>

            <div class="form-group">
                <label>Kategori</label>

                <select
                    id="edit_category"
                    required>

                    <option value="">Pilih Kategori</option>

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->category_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="form-group">
                <label>Jenis</label>

                <select
                    name="subcategory_id"
                    id="edit_subcategory"
                    required>

                    <option value="">Pilih Jenis</option>

                    @foreach($subcategories as $subcategory)
                        <option
                            value="{{ $subcategory->id }}"
                            data-category="{{ $subcategory->category_id }}">

                            {{ $subcategory->subcategory_name }}

                        </option>
                    @endforeach

                </select>
            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="closeModal('editAssetModal')">

                    Batal

                </button>

                <button
                    type="submit"
                    class="btn btn-primary">

                    Simpan Perubahan

                </button>

            </div>

        </form>

    </div>
</div>

{{-- ===================================================
     MODAL: HAPUS ASSET
=================================================== --}}
<div id="deleteAssetModal"
     class="modal-overlay"
     role="dialog"
     aria-modal="true">

    <div class="modal-box"
         style="max-width:420px; text-align:center; padding:var(--space-xl);">

        <div style="margin-bottom:var(--space-md);">
            <i class="bi bi-trash-fill"
               style="font-size:56px; color:var(--color-danger-btn);"></i>
        </div>

        <h2 style="
            font-size:18px;
            font-weight:600;
            color:var(--color-gray-900);
            margin-bottom:8px;">

            Hapus Asset?

        </h2>

        <p id="deleteAssetInfo"
           style="
            color:var(--color-gray-600);
            font-size:13px;
            margin-bottom:var(--space-lg);">

            Asset ini akan dihapus permanen.

        </p>

        <form id="formDeleteAsset"
              method="POST"
              action="">

            @csrf
            @method('DELETE')

            <div style="display:flex; gap:var(--space-sm);">

                <button
                    type="button"
                    class="btn btn-secondary"
                    style="flex:1; justify-content:center;"
                    onclick="closeModal('deleteAssetModal')">

                    <i class="bi bi-x-circle"></i>
                    Batal

                </button>

                <button
                    type="submit"
                    class="btn btn-danger"
                    style="flex:1; justify-content:center;">

                    <i class="bi bi-trash"></i>
                    Ya, Hapus

                </button>

            </div>

        </form>

    </div>

</div>

{{-- ===================================================
     MODAL: TAMBAH JENIS ASET
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

        @if($errors->hasAny(['category_id', 'subcategory_name']))
            <div class="alert alert-danger" style="margin-bottom:var(--space-md);">
                <div style="display:flex; align-items:flex-start; gap:8px;">
                    <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0; margin-top:2px;"></i>
                    <div>
                        @if($errors->has('category_id'))
                            <p style="margin:0 0 4px;">{{ $errors->first('category_id') }}</p>
                        @endif
                        @if($errors->has('subcategory_name'))
                            <p style="margin:0 0 4px;">{{ $errors->first('subcategory_name') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="/admin/subcategories/store">
            @csrf

            <div class="form-group">
                <label for="category_sub">Kategori</label>
                <select name="category_id" id="category_sub" required>
                    <option value="">— Pilih Kategori —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="subcategory_name">Jenis Aset</label>
                <input type="text" name="subcategory_name" id="subcategory_name"
                       value="{{ old('subcategory_name') }}"
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

    // ---- Modal Jenis Aset ----
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

    // ---- Buka modal otomatis sesuai jenis error ----
    @if($errors->hasAny(['category_id', 'subcategory_name']))
        openModal('modalSub');
    @elseif($errors->any())
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

    function openEditAsset(id, name, categoryId, subcategoryId){
        // Ubah action form
        document.getElementById('formEditAsset').action =
            '/admin/assets/update/' + id;

        // Isi nama asset
        document.getElementById('edit_name').value = name;

        // Pilih kategori
        const categorySelect = document.getElementById('edit_category');
        categorySelect.value = categoryId;

        // Filter subkategori sesuai kategori
        const subcategorySelect = document.getElementById('edit_subcategory');

        Array.from(subcategorySelect.options).forEach(option => {

            if(option.value === ''){
                option.hidden = false;
                return;
            }

            option.hidden =
                option.dataset.category != categoryId;

        });

        // Pilih subkategori
        subcategorySelect.value = subcategoryId;

        // Buka modal
        openModal('editAssetModal');
    }

    document.getElementById('edit_category')
    .addEventListener('change', function () {

        const categoryId = this.value;

        const subcategory =
            document.getElementById('edit_subcategory');

        subcategory.value = '';

        Array.from(subcategory.options).forEach(option => {

            if(option.value === ''){
                option.hidden = false;
                return;
            }

            option.hidden =
                option.dataset.category != categoryId;

        });

    });

    function confirmDeleteAsset(id, name)
    {
        document.getElementById('formDeleteAsset').action =
            '/admin/assets/delete/' + id;

        document.getElementById('deleteAssetInfo').innerHTML =
            'Asset <strong>' + name +
            '</strong> akan dihapus permanen dan tidak dapat dikembalikan.';

        openModal('deleteAssetModal');
    }


</script>
@endpush

@endsection