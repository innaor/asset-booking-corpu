@extends('layouts.admin')

@section('title','Activity Log')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/activity-log.css') }}">
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">
            Activity Log
        </h1>

        <p class="page-subtitle">
            Riwayat aktivitas yang dilakukan oleh SuperAdmin.
        </p>
    </div>
</div>

<div class="card">

    <div class="table-wrapper">

        <table class="schedule-table">

            <thead>

                <tr>
                    <th>Admin</th>
                    <th>Action</th>
                    <th class="description-column">
                        Description
                    </th>
                    <th class="date-column">
                        Date
                    </th>
                </tr>

            </thead>

            <tbody>

            @forelse($logs as $log)

                <tr>

                    <td>
                        {{ $log->admin->name }}
                    </td>

                    <td>

                        @if($log->action == 'change_password')

                            <span class="action-badge badge-password">
                                🔑 Change Password
                            </span>

                        @else

                            <span class="action-badge badge-impersonate">
                                👤 Impersonate
                            </span>

                        @endif

                    </td>

                    <td>

                        {{ $log->description }}

                    </td>

                    <td>

                        {{ $log->created_at->translatedFormat('d F Y - H:i:s') }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="4" style="text-align:center">

                        Belum ada aktivitas.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection