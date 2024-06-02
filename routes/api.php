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


Route::get('spin',[SpinWheelController::class,'getSpinWheel']);
Route::post('spin/store',[SpinWheelController::class,'storeSpinWheel']);
Route::post('spin/update',[SpinWheelController::class,'updateSpinWheel']);
Route::post('spin/delete',[SpinWheelController::class,'deleteSpinWheel']);

//Get the prize winner ID
Route::get('winner',[SpinWheelController::class,'getWinnerId']);

// change the status winner
Route::post('status',[SpinWheelController::class,'changeStatusWinner']);
// store user spin log
Route::post('spin-log',[SpinWheelController::class,'spinLog']);

Route::get('today-spin',[SpinWheelController::class,'checkTodaySpin']);






