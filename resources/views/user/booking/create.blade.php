@extends('layouts.user')

@section('content')

<div class="card">
    <h2>Form Booking Ruangan</h2>

    <form method="POST" action="/user/booking/store">
        @csrf

        <label>Pilih Aset</label>
        <select name="asset_id" required>
            <option value="">-- Pilih Aset --</option>
            @foreach($assets as $asset)
                <option value="{{ $asset->id }}">
                    {{ $asset->name }}
                </option>
            @endforeach
        </select>

        <label>Tanggal</label>
        <input type="date" name="date" required>

        <label>Jam Mulai</label>
        <select name="start_time" required>
            @for($i = 8; $i <= 16; $i++)
                <option value="{{ sprintf('%02d:00:00', $i) }}">
                    {{ sprintf('%02d:00', $i) }}
                </option>
            @endfor
        </select>

        <label>Jam Selesai</label>
        <select name="end_time" required>
            @for($i = 9; $i <= 17; $i++)
                <option value="{{ sprintf('%02d:00:00', $i) }}">
                    {{ sprintf('%02d:00', $i) }}
                </option>
            @endfor
        </select>

        <button type="submit" class="btn btn-primary">
            Simpan Booking
        </button>

    </form>
</div>

@endsection