@extends('layouts.admin')

@section('content')

<div class="card">
    <h2>Tambah Aset</h2>

    <form method="POST" action="/admin/assets/store">
        @csrf

        <label>Nama Aset</label>
        <input type="text" name="name" required>

        <label>Kategori</label>
        <select id="category" required>
            <option value="">-- Pilih Kategori --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>

        <label>Subkategori</label>
        <select name="subcategory_id" id="subcategory" required>
            <option value="">-- Pilih Subkategori --</option>
        </select>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

{{-- JS DINAMIS --}}
<script>

    const subcategories = @json($subcategories);

    document.getElementById('category').addEventListener('change', function() {

        let categoryId = this.value;

        let subcategoryDropdown = document.getElementById('subcategory');

        subcategoryDropdown.innerHTML = '<option value="">-- Pilih Subkategori --</option>';

        subcategories.forEach(function(sub) {

            if(sub.category_id == categoryId){

                subcategoryDropdown.innerHTML += 
                    `<option value="${sub.id}">${sub.subcategory_name}</option>`;
            }

        });

    });

</script>

@endsection