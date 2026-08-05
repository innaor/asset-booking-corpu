<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Asset;
use Carbon\Carbon;
use App\Exports\BookingExport;
use Maatwebsite\Excel\Facades\Excel;

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
            'kepentingan' => 'required|string|max:225',
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
            'kepentingan' => $request->kepentingan,
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
            'kepentingan'  => 'required|string|max:255',
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
            'kepentingan' => $request->kepentingan,
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

    public function export(Request $request)
    {
        $query = Booking::with(['asset', 'user']);

        // Filter berdasarkan rentang tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {

            $query->whereBetween('date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $bookings = $query
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return Excel::download(
            new BookingExport($bookings),
            'Data Booking.xlsx'
        );
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

    // public function export(Request $request)
    // {
    //     return Excel::download(
    //         new BookingExport,
    //         'Data Booking ' . now()->format('d-m-Y') . '.xlsx'
    //     );
    // }


    // Method Check in
    public function checkin(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'approved') {
            return back()->with('error', 'Check-in hanya bisa dilakukan pada booking dengan status Approved.');
        }

        $request->validate([
            'checkin_condition' => 'required|in:baik,rusak_ringan,rusak_berat',
            'checkin_note'       => 'nullable|string',
            'checkin_photo'      => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('checkin_photo')) {
            $path = $request->file('checkin_photo')->store('booking-conditions', 'public');
        }

        $booking->update([
            'status'             => 'ongoing',
            'checkin_condition'  => $request->checkin_condition,
            'checkin_note'       => $request->checkin_note,
            'checkin_photo'      => $path,
            'checkin_at'         => now(),
            'checkin_by'         => auth()->id(),
        ]);

        return back()->with('success', 'Check-in berhasil dicatat. Status booking menjadi Ongoing.');
    }

    public function checkout(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'ongoing') {
            return back()->with('error', 'Check-out hanya bisa dilakukan pada booking dengan status Ongoing.');
        }

        $request->validate([
            'checkout_condition' => 'required|in:baik,rusak_ringan,rusak_berat',
            'checkout_note'       => 'nullable|string',
            'checkout_photo'      => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('checkout_photo')) {
            $path = $request->file('checkout_photo')->store('booking-conditions', 'public');
        }

        $booking->update([
            'status'              => 'completed',
            'checkout_condition'  => $request->checkout_condition,
            'checkout_note'       => $request->checkout_note,
            'checkout_photo'      => $path,
            'checkout_at'         => now(),
            'checkout_by'         => auth()->id(),
        ]);

        return back()->with('success', 'Check-out berhasil dicatat. Status booking menjadi Completed.');
    }
}