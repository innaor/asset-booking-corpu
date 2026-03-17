<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetController;
use App\Models\Asset;
use App\Http\Controllers\BookingController;
use App\Models\Booking;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);


/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])->group(function () {

    Route::get('/admin/dashboard', function (Request $request) {

        $date = $request->date ?? date('Y-m-d');

        $assets = Asset::where('status', 'published')->get();

        $bookings = Booking::where('date', $date)
            ->whereIn('status', ['pending', 'approved', 'ongoing'])
            ->get();

        return view('admin.dashboard', compact('assets', 'bookings', 'date'));
    });


    Route::get('/admin/assets', [AssetController::class, 'index']);
    Route::post('/admin/assets/store', [AssetController::class, 'store']);
    Route::get('/admin/assets/toggle/{id}', [AssetController::class, 'toggleStatus']);

});


/*
|--------------------------------------------------------------------------
| USER DASHBOARD
|--------------------------------------------------------------------------
*/
Route::middleware(['internal'])->group(function () {

    Route::get('/user/dashboard', function (\Illuminate\Http\Request $request) {

        $date = $request->date ?? date('Y-m-d');

        $assets = Asset::where('status', 'published')->get();

        $bookings = Booking::where('date', $date)
            ->whereIn('status', ['pending', 'approved', 'ongoing'])
            ->get();

        return view('user.dashboard', compact('assets', 'bookings', 'date'));
    });

});

/*
|--------------------------------------------------------------------------
| DEFAULT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/login');
});