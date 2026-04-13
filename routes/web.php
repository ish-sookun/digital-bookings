<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SalesPerformanceController;
use App\Http\Controllers\SalespersonController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (HomeController $controller) {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if ($user->isFinance()) {
        return redirect()->route('reservations.index');
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

    // Admin & Super Admin: manage salespeople, platforms, placements, and delete clients/agencies
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('salespeople', SalespersonController::class)->except(['show']);
        Route::resource('placements', PlacementController::class);
        Route::resource('platforms', PlatformController::class);
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::delete('agencies/{agency}', [AgencyController::class, 'destroy'])->name('agencies.destroy');
    });

    // Dashboard & Calendar: super_admin, admin, salesperson, management
    Route::middleware('role:super_admin,admin,salesperson,management')->group(function () {
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    });

    // Clients & Agencies: super_admin, admin, salesperson
    Route::middleware('role:super_admin,admin,salesperson')->group(function () {
        Route::resource('clients', ClientController::class)->except(['destroy']);
        Route::resource('agencies', AgencyController::class)->except(['destroy']);
    });

    // Reservations write: super_admin, admin, salesperson
    Route::middleware('role:super_admin,admin,salesperson')->group(function () {
        Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
        Route::put('reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
        Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
        Route::post('reservations/{reservation}/upload-document', [ReservationController::class, 'uploadDocument'])->name('reservations.upload-document');
    });

    // Reservations read: super_admin, admin, salesperson, finance
    Route::middleware('role:super_admin,admin,salesperson,finance')->group(function () {
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::get('reservations/{reservation}/pdf', [ReservationController::class, 'downloadPdf'])->name('reservations.pdf');
        Route::get('reservations/{reservation}/document/{type}', [ReservationController::class, 'downloadDocument'])->name('reservations.document');
    });

    // Sales performance export: super_admin, management
    Route::middleware('role:super_admin,management')->group(function () {
        Route::get('sales-performance/export', [SalesPerformanceController::class, 'export'])
            ->name('sales-performance.export');
    });

    // All authenticated roles
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::get('/profile', [UserController::class, 'profile'])->name('profile.show');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');
});

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
})->name('echo');
