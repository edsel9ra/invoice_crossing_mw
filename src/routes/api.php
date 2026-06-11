<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CrossingController;
use App\Http\Controllers\InvoiceCrossingController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('stats', [CrossingController::class, 'stats']);

Route::get('branches', [BranchController::class, 'index']);

Route::get('clients', [ClientController::class, 'index']);
Route::get('clients/by-doc/{docNum}', [ClientController::class, 'byDoc']);
Route::post('clients', [ClientController::class, 'store']);
Route::get('clients/{client}', [ClientController::class, 'show']);

Route::get('items', [ItemController::class, 'index']);
Route::post('items', [ItemController::class, 'store']);

Route::post('clients/{client}/cross', [InvoiceCrossingController::class, 'store']);
Route::get('clients/{client}/crossings', [CrossingController::class, 'clientCrossings']);

Route::get('crossings', [CrossingController::class, 'index']);
Route::get('crossings/export', [CrossingController::class, 'export']);
Route::get('crossings/{crossing}', [CrossingController::class, 'show']);
