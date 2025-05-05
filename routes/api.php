<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\FoundationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DonationRequestController;
use App\Http\Controllers\Api\AIModelController;
use App\Http\Controllers\Api\AiRecommendationController;
use App\Http\Controllers\DonationController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
#-------------------------------------------------------------------------------------
Route::post('/register/donor', [DonorController::class, 'register']);
#--------------------------------------------------------------------------------------
Route::post('/register/foundation', [FoundationController::class, 'register']);
#----------------------------------------------------------------------------------------
Route::post('/login', [AuthController::class, 'login'])->name('login');
#---------------------------------------------------------------------------------------
Route::get('/donor/{id}', [DonorController::class, 'show']);
#------------------------------------------------------------------------------------
Route::get('/foundation/{id}', [FoundationController::class, 'show']);
#--------------------------------------------------------------------------------------
Route::post('/update-profile', [AuthController::class, 'updateProfile']);
#-------------------------------------------------------------------------------------
Route::post('/update-password', [AuthController::class, 'updatePassword']);
#--------------------------------------------------------------------------------------------
Route::apiResource('donation-requests', DonationRequestController::class);
#--------------------------------------------------------------------------------------------
Route::get('/foundations', [AuthController::class, 'getAllFoundations']);
#-----------------------------------------------------------------------------------------
Route::get('/donors', [AuthController::class, 'getAllDonors']);
#-----------------------------------------------------------------------------------------
Route::get('/donation-requests', [DonationRequestController::class, 'GetAllrequest']);
#---------------------------------------------------------------------------------------
Route::get('/donation-requests/{id}', [DonationRequestController::class, 'ShowOneRequest']);
#---------------------------------------------------------------------------------------
Route::get('/foundations/{foundationId}/donation-requests', [DonationRequestController::class, 'getRequestsByFoundationId']);
#--------------------------------------------------------------------------------------------
#دا بيخزن الدفع
Route::post('/donations', [DonationController::class, 'store']);
#---------------------------------------------------------------------------------------------------
#دابيعرض بيانات تبرع معين
Route::get('/donations/{id}', [DonationController::class, 'show']);
#---------------------------------------------------------------------------------------------
#دا بيعرض احصائيات تبرع معين
Route::get('/donations/request/{requestId}/stats', [DonationController::class, 'getRequestStats']);
#----------------------------------------------------------------------------------------------------
#دا بيعرض تبرعات متبرع معين
Route::get('/donations/donor/{donorId}', [DonationController::class, 'getDonorDonations']);
#----------------------------------------------------------------------------------------------------
# دا رابط المودل
Route::get('/ai/recommend/{donorId}', [AiRecommendationController::class, 'recommendations_Ai']);

#----------------------------------------------
#---------بيجيب الطلبات على حسب الموقع-------
Route::get('/requests/by-location', [DonationRequestController::class, 'getRequestsByLocation']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
