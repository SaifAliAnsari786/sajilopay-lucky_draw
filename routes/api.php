<?php

use App\Http\Controllers\SpinWheelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Spin prizes
Route::group(['prefix' => 'spin-prize'], function () {
    Route::get('/',[SpinWheelController::class, 'list']);
    Route::post('/',[SpinWheelController::class, 'store']);
    Route::put('/{id}',[SpinWheelController::class, 'update']);
    Route::delete('/{id}',[SpinWheelController::class, 'delete']);
});

// Prize Winner
Route::group(['prefix' => 'winner'], function () {
    Route::post('store-winner',[SpinWheelController::class, 'storeWinner']);
    Route::put('change-status/{id}',[SpinWheelController::class, 'changeStatusWinner']);
});

// Store user spin log
Route::post('store-spin-log',[SpinWheelController::class, 'storeSpinLog']);

// Check available spin
Route::post('check-spin',[SpinWheelController::class, 'checkTodaySpin']);

// Public APIs
Route::get('spin-prize/get-list', [SpinWheelController::class, 'getList']);
