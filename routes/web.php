<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SalespersonController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (HomeController $controller) {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return $controller->index();
})->name('home');

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [UserController::class, 'login'])->name('login.store');

Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Routes requiring authentication
Route::middleware('auth')->group(function () {

    // Super Admin only: User management & Budgets
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show']);

        Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
        Route::get('budgets/{platform}/{year}/{month}/edit', [BudgetController::class, 'edit'])
            ->whereNumber(['year', 'month'])
            ->name('budgets.edit');
        Route::put('budgets/{platform}/{year}/{month}', [BudgetController::class, 'update'])
            ->whereNumber(['year', 'month'])
            ->name('budgets.update');
    });

    // Admin & Super Admin: manage clients, agencies, salespeople, platforms, placements
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('salespeople', SalespersonController::class)->except(['show']);
        Route::resource('agencies', AgencyController::class);
        Route::resource('clients', ClientController::class);
        Route::resource('placements', PlacementController::class);
        Route::resource('platforms', PlatformController::class);
    });

    // All roles: reservations and calendar
    Route::resource('reservations', ReservationController::class);
    Route::get('reservations/{reservation}/pdf', [ReservationController::class, 'downloadPdf'])->name('reservations.pdf');
    Route::get('reservations/{reservation}/document/{type}', [ReservationController::class, 'downloadDocument'])->name('reservations.document');
    Route::post('reservations/{reservation}/upload-document', [ReservationController::class, 'uploadDocument'])->name('reservations.upload-document');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::get('/profile', [UserController::class, 'profile'])->name('profile.show');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');
});

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
})->name('echo');
