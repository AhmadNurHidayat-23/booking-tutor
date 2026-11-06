<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TutorController;
use App\Http\Middleware\EnsureStaff;
use Illuminate\Support\Facades\Route;

Route::get('/', [TutorController::class, 'index']);
Route::get('/tutor/{tutor}', [TutorController::class, 'show']);
Route::get('/masuk', [AuthController::class, 'form'])->middleware('guest')->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/daftar', [AuthController::class, 'register'])->middleware(['guest', 'throttle:5,1']);
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout']);
    Route::get('/reservasi', [BookingController::class, 'index']);
    Route::post('/jadwal/{slot}/pesan', [BookingController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/reservasi/{booking}', [BookingController::class, 'show']);
    Route::patch('/reservasi/{booking}', [BookingController::class, 'update']);
    Route::middleware(EnsureStaff::class)->prefix('petugas')->group(function () {
        Route::get('/tutor', [TutorController::class, 'manage']);
        Route::get('/tutor/baru', [TutorController::class, 'edit']);
        Route::get('/tutor/{tutor}/edit', [TutorController::class, 'edit']);
        Route::post('/tutor/{tutor?}', [TutorController::class, 'save']);
        Route::post('/tutor/{tutor}/jadwal', [TutorController::class, 'addSlot']);
        Route::patch('/jadwal/{slot}', [TutorController::class, 'toggle']);
    });
});
