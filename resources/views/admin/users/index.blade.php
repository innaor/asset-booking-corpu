@extends('layouts.admin')

@section('title', 'User Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/user-management.css') }}">
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">
            Daftar seluruh pengguna yang telah memiliki akun.
        </p>
    </div>
</div>

<div class="card">

    <div class="table-wrapper">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Status</th>
                    <th style="width: 80px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ ucfirst($user->status) }}</td>
                        <td class="action-column">
                            <button
                                class="action-btn"
                                onclick="toggleDropdown({{ $user->id }})">
                                ⋮
                            </button>
                            <div
                                class="action-dropdown"
                                id="dropdown-{{ $user->id }}">
                                <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="openPasswordModal({{ $user->id }})">
                                    Ganti Password
                                </button>
                                <!-- <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="openImpersonateModal({{ $user->id }})">
                                    Impersonate
                                </button> -->
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;">
                            Belum ada user.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>


<!-- modal password -->
<div class="modal-overlay" id="passwordModal">

    <div class="modal-box">

        <div class="modal-header">

            <div>
                <h2 class="modal-title">
                    Ganti Password
                </h2>

                <p class="modal-subtitle">
                    Masukkan password baru untuk pengguna.
                </p>
            </div>

            <button
                type="button"
                class="modal-close"
                onclick="closePasswordModal()">

                <i class="bi bi-x-lg"></i>

            </button>

        </div>

        <form id="passwordForm" method="POST">

            @csrf

            <div class="form-group">

                <label>Password Baru</label>

                <input
                    type="password"
                    name="password"
                    required>

            </div>

            <div class="form-group">

                <label>Konfirmasi Password</label>

                <input
                    type="password"
                    name="password_confirmation"
                    required>

            </div>

            <div class="modal-footer">

                <button
                    type="submit"
                    class="btn btn-primary">

                    Simpan

                </button>

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="closePasswordModal()">

                    Batal

                </button>

            </div>

        </form>

    </div>

</div>

<!-- modal impersonate -->
<div class="modal-overlay" id="impersonateModal">

    <div class="modal-box">

        <div class="modal-header">

            <div>
                <h2 class="modal-title">
                    Impersonate User
                </h2>

                <p class="modal-subtitle">
                    Masukkan alasan sebelum masuk sebagai user.
                </p>
            </div>

            <button
                type="button"
                class="modal-close"
                onclick="closeImpersonateModal()">

                <i class="bi bi-x-lg"></i>

            </button>

        </div>

        <form id="impersonateForm" method="POST">

            @csrf

            <div class="form-group">

                <label>Alasan Impersonate</label>

                <textarea
                    name="reason"
                    rows="4"
                    placeholder="Contoh: Membantu user melakukan booking ruangan."
                    required></textarea>

            </div>

            <div class="modal-footer">

                <button
                    type="submit"
                    class="btn btn-primary">

                    Masuk sebagai User

                </button>

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="closeImpersonateModal()">

                    Batal

                </button>

            </div>

        </form>

    </div>

</div>

<script>

function toggleDropdown(id)
{
    document
        .querySelectorAll('.action-dropdown')
        .forEach(dropdown => {
            dropdown.classList.remove('show');
        });

    document
        .getElementById('dropdown-' + id)
        .classList.toggle('show');
}

document.addEventListener('click', function(e){

    if(!e.target.closest('.action-column'))
    {
        document
            .querySelectorAll('.action-dropdown')
            .forEach(dropdown => {
                dropdown.classList.remove('show');
            });
    }

});

function openPasswordModal(id)
{
    document
        .getElementById('passwordForm')
        .action =
            '/admin/users/' +
            id +
            '/change-password';

    document
        .getElementById('passwordModal')
        .classList.add('active');
}

function closePasswordModal()
{
    document
        .getElementById('passwordModal')
        .classList.remove('active');
}

document
    .getElementById('passwordModal')
    .addEventListener('click', function(e){

        if(e.target === this)
        {
            closePasswordModal();
        }

});

function openImpersonateModal(id)
{
    document
        .getElementById('impersonateForm')
        .action =
            '/admin/users/' +
            id +
            '/impersonate';

    document
        .getElementById('impersonateModal')
        .classList.add('active');
}

function closeImpersonateModal()
{
    document
        .getElementById('impersonateModal')
        .classList.remove('active');
}

document
    .getElementById('impersonateModal')
    .addEventListener('click', function(e){

        if(e.target === this)
        {
            closeImpersonateModal();
        }

});

</script>

@endsection