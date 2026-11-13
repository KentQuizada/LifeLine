<?php

use App\Http\Controllers\Admin\DonorVerificationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/', 'onboarding')->name('onboarding');

    Route::get('/register', [RegisterController::class, 'showStep1'])->name('register.step1');
    Route::post('/register', [RegisterController::class, 'storeStep1'])->middleware('throttle:registration');
    Route::get('/register/medical', [RegisterController::class, 'showStep2'])->name('register.step2');
    Route::post('/register/medical', [RegisterController::class, 'storeStep2'])->middleware('throttle:registration');
    Route::get('/register/account', [RegisterController::class, 'showStep3'])->name('register.step3');
    Route::post('/register/account', [RegisterController::class, 'store'])->middleware('throttle:registration');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', HomeController::class)->name('home');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/donors/pending', [DonorVerificationController::class, 'index'])->name('donors.pending');
    Route::post('/donors/{donor}/verify', [DonorVerificationController::class, 'verify'])->name('donors.verify');
    Route::post('/donors/{donor}/reject', [DonorVerificationController::class, 'reject'])->name('donors.reject');
});
