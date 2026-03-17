<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Asset;

class BookingController extends Controller
{
    public function create()
    {
        $assets = Asset::where('status', 'published')->get();
        return view('user.booking.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        Booking::create([
            'asset_id' => $request->asset_id,
            'user_id' => auth()->id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'pending'
        ]);

        return redirect('/user/dashboard')->with('success', 'Booking berhasil dibuat');
    }
}