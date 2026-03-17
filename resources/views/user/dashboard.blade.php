@extends('layouts.user')

@section('content')

<div class="card">
    <h2>Selamat datang, {{ auth()->user()->name }} 👋</h2>
</div>

<div class="schedule-wrapper">

    <div class="schedule-toolbar">

        <div class="left">
            <a href="/user/booking/create" class="btn btn-primary">
                + Booking Ruangan
            </a>
        </div>

        <div class="right">
            <form method="GET" action="">
                <label>Pilih Tanggal</label>
                <input type="date" name="date" value="{{ $date }}">

                <button type="submit" class="btn btn-primary">
                    Search
                </button>
            </form>
        </div>

    </div>

    <table class="schedule-table">

        <thead>
            <tr>
                <th>Aset</th>
                <th>08:00</th>
                <th>09:00</th>
                <th>10:00</th>
                <th>11:00</th>
                <th>12:00</th>
                <th>13:00</th>
                <th>14:00</th>
                <th>15:00</th>
                <th>16:00</th>
            </tr>
        </thead>

        <tbody>
        @foreach($assets as $asset)
        <tr>
            <td class="asset-name-col">
                {{ $asset->name }}
            </td>

            @for($hour = 8; $hour <= 16; $hour++)

                @php
                    $slotTime = sprintf('%02d:00:00', $hour);

                    $booking = $bookings->first(function($b) use ($asset, $hour) {
                        return $b->asset_id == $asset->id &&
                            (int)date('H', strtotime($b->start_time)) <= $hour &&
                            (int)date('H', strtotime($b->end_time)) > $hour;
                    });
                @endphp

                @if($booking)

                    @php
                        $class = '';
                        if($booking->status == 'pending') $class = 'slot-pending';
                        if($booking->status == 'approved') $class = 'slot-approved';
                        if($booking->status == 'ongoing') $class = 'slot-ongoing';
                    @endphp

                    <td class="time-slot {{ $class }}">
                        <div>
                            {{ date('H:i', strtotime($booking->start_time)) }}
                            -
                            {{ date('H:i', strtotime($booking->end_time)) }}
                        </div>
                        <small>{{ ucfirst($booking->status) }}</small>
                    </td>

                @else

                    <td class="time-slot">-</td>

                @endif

            @endfor
        </tr>
        @endforeach
        </tbody>

    </table>

</div>

@endsection