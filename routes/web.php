<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetController;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login']);
Route::get('/logout',   [AuthController::class, 'logout']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register',[AuthController::class, 'register']);


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])->group(function () {

    // Dashboard Admin
    Route::get('/admin/dashboard', function (Request $request) {

        $date = $request->date ?? date('Y-m-d');

        $assets = Asset::with('subcategory.category')   // eager load untuk data-category-id
                       ->where('status', 'published')
                       ->get();

        $bookings = Booking::where('date', $date)
                           ->whereIn('status', ['pending', 'approved', 'ongoing'])
                           ->get();

        $categories = Category::all();                  // ← tambahan untuk filter kategori

        return view('admin.dashboard', compact('assets', 'bookings', 'date', 'categories'));
    });
    // User Management
    Route::get('/admin/users', [\App\Http\Controllers\AdminUserController::class, 'index']);

    // Assets
    Route::get('/admin/assets',                  [AssetController::class, 'index']);
    Route::post('/admin/assets/store',           [AssetController::class, 'store']);
    Route::get('/admin/assets/toggle/{id}',      [AssetController::class, 'toggleStatus']);
    Route::post('/admin/subcategories/store',    [AssetController::class, 'storeSubcategory']);

    // Booking Admin
    Route::get('/admin/booking', function () {
        $bookings = Booking::with(['asset', 'user'])->latest()->get();
        return view('admin.booking.index', compact('bookings'));
    });

    Route::post('/admin/booking/update-status/{id}', [BookingController::class, 'updateStatus']);
    Route::delete('/admin/booking/{id}',             [BookingController::class, 'destroy'])->name('booking.destroy');

    Route::post(
        '/admin/users/{id}/change-password',
        [AdminUserController::class, 'changePassword']
    )->name('admin.users.change-password');


    //route impersonate
    Route::post(
        '/admin/users/{id}/impersonate',
        [AdminUserController::class, 'impersonate']
    )->name('admin.users.impersonate');


    //route activity log
    Route::get('/admin/activity-log', [ActivityLogController::class, 'index']);

});


/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['internal'])->group(function () {

    // Dashboard User
    Route::get('/user/dashboard', function (Request $request) {

        $date = $request->date ?? date('Y-m-d');

        $assets = Asset::with('subcategory.category')   // eager load untuk data-category-id
                       ->where('status', 'published')
                       ->get();

        $bookings = Booking::where('date', $date)
                           ->whereIn('status', ['pending', 'approved', 'ongoing', 'completed'])
                           ->get();

        $categories = Category::all();                  // ← tambahan untuk filter kategori

        return view('user.dashboard', compact('assets', 'bookings', 'date', 'categories'));
    });

    // Booking User
    Route::get('/user/booking/create', function () {
        $assets = Asset::where('status', 'published')->get();
        return view('user.booking.create', compact('assets'));
    });

    Route::post('/user/booking/store',           [BookingController::class, 'store']);
    Route::get('/user/booking',                  [BookingController::class, 'index']);
    Route::get('/user/booking/edit/{id}',        [BookingController::class, 'edit']);
    Route::post('/user/booking/update/{id}',     [BookingController::class, 'update']);
    Route::delete('/user/booking/delete/{id}',   [BookingController::class, 'destroy']);

});


/*
|--------------------------------------------------------------------------
| DEFAULT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/login');
});