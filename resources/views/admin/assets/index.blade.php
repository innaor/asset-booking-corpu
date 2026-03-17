@extends('layouts.admin')

@section('content')

<div class="card">
    <h2>Data Aset</h2>

    <button class="btn btn-primary" onclick="openModal()">+ Tambah Aset</button>

    <br><br>

    <table>
        <tr>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Subkategori</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        @foreach($assets as $asset)
        <tr>
            <td>{{ $asset->name }}</td>
            <td>{{ $asset->subcategory->category->category_name }}</td>
            <td>{{ $asset->subcategory->subcategory_name }}</td>
            <td>@if($asset->status == 'published')
                    <span class="badge badge-success">Published</span>
                @else
                    <span class="badge badge-draft">Draft</span>
                @endif
            </td>
            <td>
                @if($asset->status == 'draft')
                    <a href="/admin/assets/toggle/{{ $asset->id }}" class="btn btn-success">
                        Publish
                    </a>
                @else
                    <a href="/admin/assets/toggle/{{ $asset->id }}" class="btn btn-secondary">
                        Unpublish
                    </a>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</div>

<div id="assetModal" class="modal">

    <div class="modal-content">

        <span class="close" onclick="closeModal()">&times;</span>

        <h3>Tambah Aset</h3>

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

            <br>
            <button type="submit" class="btn btn-primary">Simpan</button>

        </form>

    </div>

</div>

<script>

function openModal(){
    document.getElementById("assetModal").style.display = "block";
}

function closeModal(){
    document.getElementById("assetModal").style.display = "none";
}

const subcategories = @json($subcategories);

document.getElementById('category').addEventListener('change', function(){

    let categoryId = this.value;
    let dropdown = document.getElementById('subcategory');

    dropdown.innerHTML = '<option value="">-- Pilih Subkategori --</option>';

    subcategories.forEach(function(sub){

        if(sub.category_id == categoryId){
            dropdown.innerHTML += `<option value="${sub.id}">${sub.subcategory_name}</option>`;
        }

    });

});

</script>

@endsection