<?php

namespace App\Exports;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return Booking::with(['user','asset'])
            ->get()
            ->map(function ($booking) {

                return [

                    'Nama Peminjam' => $booking->user->name ?? '-',

                    'Kontak' => $booking->user->phone ?? '-',

                    'Aset' => $booking->asset->name ?? '-',

                    'Tanggal' => $booking->date,

                    'Jam' => $booking->start_time.' - '.$booking->end_time,

                    'Status' => ucfirst($booking->status),

                ];

            });
    }

    public function headings(): array
    {
        return [

            'Nama Peminjam',

            'Kontak',

            'Aset',

            'Tanggal',

            'Jam',

            'Status',

        ];
    }
}