<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', fn () => Inertia::render('Crossings/Create'))->name('home');
Route::get('/cruces/nuevo', fn () => Inertia::render('Crossings/Create'))->name('crossings.create');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/clientes', fn () => Inertia::render('Clients/Index'))->name('clients.index');
    Route::get('/items', fn () => Inertia::render('Items/Index'))->name('items.index');
    Route::get('/sedes', fn () => Inertia::render('Branches/Index'))->name('branches.index');
    Route::get('/cruces', fn () => Inertia::render('Crossings/Index'))->name('crossings.index');
    Route::get('/cruces/{crossing}', fn (int $crossing) => Inertia::render('Crossings/Show', ['id' => $crossing]))
        ->whereNumber('crossing')
        ->name('crossings.show');
});

Route::get('/tickets/{ticket}/descargar', [TicketController::class, 'download'])
    ->name('tickets.download');
