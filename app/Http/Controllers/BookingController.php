<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Asset;
use Carbon\Carbon;

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
            'asset_id'   => 'required',
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        $now       = Carbon::now();
        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime   = Carbon::parse($request->date . ' ' . $request->end_time);

        if ($startTime->lte($now)) {
            return back()
                ->withErrors(['start_time' => 'Tidak dapat melakukan booking pada tanggal atau jam yang sudah berlalu.'])
                ->withInput();
        }

        if ($endTime->lte($startTime)) {
            return back()
                ->withErrors(['end_time' => 'Jam selesai harus setelah jam mulai.'])
                ->withInput();
        }

        $conflict = Booking::where('asset_id', $request->asset_id)
            ->where('date', $request->date)
            ->whereIn('status', ['pending', 'approved', 'ongoing'])
            ->where(function ($q) use ($request) {
                $q->where('start_time', '<', $request->end_time)
                  ->where('end_time', '>', $request->start_time);
            })->exists();

        if ($conflict) {
            $assetName = Asset::find($request->asset_id)->name;
            return back()
                ->withErrors(['asset_id' => $assetName . ' sudah dipesan pada waktu tersebut. Silakan pilih waktu lain.'])
                ->withInput();
        }

        Booking::create([
            'asset_id'   => $request->asset_id,
            'user_id'    => auth()->id(),
            'date'       => $request->date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'status'     => 'pending',
        ]);

        return redirect('/user/dashboard')->with('booking_success', 'Booking Anda berhasil diajukan! Silakan tunggu konfirmasi dari admin.');
    }

    public function index()
    {
        $bookings = Booking::with('asset')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $assets = Asset::where('status', 'published')->get();

        return view('user.booking.index', compact('bookings', 'assets'));
    }

    public function edit($id)
    {
        $booking = Booking::where('id', $id)
                          ->where('user_id', auth()->id())
                          ->firstOrFail();

        $assets = Asset::where('status', 'published')->get();

        return view('user.booking.index', compact('booking', 'assets'))->with([
            'bookings' => Booking::with('asset')->where('user_id', auth()->id())->latest()->get(),
            'editMode' => true,
        ]);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
                          ->where('user_id', auth()->id())
                          ->firstOrFail();

        $request->validate([
            'asset_id'   => 'required',
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        $now       = Carbon::now();
        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime   = Carbon::parse($request->date . ' ' . $request->end_time);

        if ($startTime->lte($now)) {
            return back()
                ->withErrors(['start_time' => 'Tidak dapat mengubah booking ke tanggal atau jam yang sudah berlalu.'])
                ->withInput();
        }

        if ($endTime->lte($startTime)) {
            return back()
                ->withErrors(['end_time' => 'Jam selesai harus setelah jam mulai.'])
                ->withInput();
        }

        // Cek konflik, kecualikan booking milik diri sendiri
        $conflict = Booking::where('asset_id', $request->asset_id)
            ->where('date', $request->date)
            ->where('id', '!=', $id)
            ->whereIn('status', ['pending', 'approved', 'ongoing'])
            ->where(function ($q) use ($request) {
                $q->where('start_time', '<', $request->end_time)
                  ->where('end_time', '>', $request->start_time);
            })->exists();

        if ($conflict) {
            $assetName = Asset::find($request->asset_id)->name;
            return back()
                ->withErrors(['asset_id' => $assetName . ' sudah dipesan pada waktu tersebut. Silakan pilih waktu lain.'])
                ->withInput();
        }

        $booking->update([
            'asset_id'   => $request->asset_id,
            'date'       => $request->date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        return redirect('/user/booking')->with('edit_success', 'Booking berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        return redirect()->back()->with('delete_success', 'Booking berhasil dihapus.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required']);
        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();
        return back()->with('success', 'Status booking berhasil diupdate.');
    }

    public function autoUpdateStatus()
    {
        $now = Carbon::now();
        $bookings = Booking::whereIn('status', ['approved', 'ongoing'])->get();

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->date . ' ' . $booking->start_time);
            $end   = Carbon::parse($booking->date . ' ' . $booking->end_time);

            if ($booking->status === 'approved' && $now->gte($start) && $now->lte($end)) {
                $booking->update(['status' => 'ongoing']);
            }
            if ($booking->status === 'ongoing' && $now->gt($end)) {
                $booking->update(['status' => 'completed']);
            }
        }
    }
}