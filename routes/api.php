<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\FoundationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DonationRequestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
#-------------------------------------------------------
Route::post('/register/donor', [DonorController::class, 'register']);
Route::post('/register/foundation', [FoundationController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/donor/{id}', [DonorController::class, 'show']);
Route::get('/foundation/{id}', [FoundationController::class, 'show']);
Route::post('/update-profile', [AuthController::class, 'updateProfile']);
Route::post('/update-password', [AuthController::class, 'updatePassword']);
Route::apiResource('donation-requests', DonationRequestController::class);
Route::get('/foundations', [AuthController::class, 'getAllFoundations']);
Route::get('/donors', [AuthController::class, 'getAllDonors']);
#------------------------------------------------------------------------
