<?php

use App\Http\Controllers\AdminBannerController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMessageController;
use App\Http\Controllers\AdminPackageController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PackageController;
use App\Models\BannerSetting;
use App\Models\Package;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Featured Tours: the 3 newest active packages.
    $featuredPackages = Package::where('status', true)
        ->latest()
        ->take(3)
        ->get();

    // Editable banner (only one settings row exists; null if it was never created).
    $banner = BannerSetting::first();

    return view('home', compact('banner', 'featuredPackages'));
});

Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/{package}', [PackageController::class, 'show']);

Route::get('/contact', [ContactController::class, 'create']);
Route::post('/contact', [ContactController::class, 'store']);

Route::get('/booking', [BookingController::class, 'create']);
Route::post('/booking', [BookingController::class, 'store']);

// Admin: login pages are for guests only (max 5 login attempts per minute).
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminController::class, 'showLogin']);
    Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');
});

// Admin: these pages need a logged-in user.
Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);

    // Admin package management
    Route::get('/admin/packages', [AdminPackageController::class, 'index']);
    Route::get('/admin/packages/create', [AdminPackageController::class, 'create']);
    Route::post('/admin/packages', [AdminPackageController::class, 'store']);
    Route::get('/admin/packages/{package}/edit', [AdminPackageController::class, 'edit']);
    Route::match(['put', 'patch'], '/admin/packages/{package}', [AdminPackageController::class, 'update']);
    Route::delete('/admin/packages/{package}', [AdminPackageController::class, 'destroy']);

    // Admin banner settings (the Home page banner)
    Route::get('/admin/banner', [AdminBannerController::class, 'index']);
    Route::match(['put', 'patch'], '/admin/banner', [AdminBannerController::class, 'update']);

    // Admin booking requests
    Route::get('/admin/bookings', [AdminBookingController::class, 'index']);
    Route::get('/admin/bookings/{booking}', [AdminBookingController::class, 'show']);
    Route::post('/admin/bookings/{booking}/read', [AdminBookingController::class, 'markRead']);

    // Admin message inbox
    Route::get('/admin/messages', [AdminMessageController::class, 'index']);
    Route::get('/admin/messages/{contact}', [AdminMessageController::class, 'show']);
    Route::post('/admin/messages/{contact}/read', [AdminMessageController::class, 'markRead']);
});
