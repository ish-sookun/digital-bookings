<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SalespersonController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return view('home');
})->name('home');

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [UserController::class, 'login'])->name('login.store');

Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::resource('salespeople', SalespersonController::class)->except(['show']);

Route::resource('agencies', AgencyController::class);

Route::resource('clients', ClientController::class);

Route::resource('placements', PlacementController::class);

Route::resource('platforms', PlatformController::class);

Route::resource('reservations', ReservationController::class);
Route::get('reservations/{reservation}/pdf', [ReservationController::class, 'downloadPdf'])->name('reservations.pdf');

Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

Route::get('/profile', [UserController::class, 'profile'])->name('profile.show');
Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
})->name('echo');
