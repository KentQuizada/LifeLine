<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\DonorVerificationController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BloodRequestController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonorMatchController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestDonorController;
use Illuminate\Support\Facades\Route;

Route::view('/track', 'requests.track')->name('track');

Route::middleware('guest')->group(function () {
    Route::view('/', 'onboarding')->name('onboarding');

    Route::get('/register', [RegisterController::class, 'showStep1'])->name('register.step1');
    Route::post('/register', [RegisterController::class, 'storeStep1'])->middleware('throttle:registration');
    Route::get('/register/medical', [RegisterController::class, 'showStep2'])->name('register.step2');
    Route::post('/register/medical', [RegisterController::class, 'storeStep2'])->middleware('throttle:registration');
    Route::get('/register/account', [RegisterController::class, 'showStep3'])->name('register.step3');
    Route::post('/register/account', [RegisterController::class, 'store'])->middleware('throttle:registration');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])
        ->middleware('throttle:6,1,password-email')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:6,1,password-reset')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1,verify-email'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1,verification-send')
        ->name('verification.send');
    Route::put('/email/verify/address', [EmailVerificationController::class, 'updateEmail'])
        ->middleware('throttle:6,1,verification-address')
        ->name('verification.address');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', HomeController::class)->name('home');
});

Route::middleware(['auth', 'verified', 'role:donor,requester'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:10,1,profile-update')->name('profile.update');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{notification}', [AlertController::class, 'open'])
        ->where('notification', '[0-9]{1,18}')
        ->name('alerts.open');

    Route::get('/requests', [BloodRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/new', [BloodRequestController::class, 'create'])->name('requests.create');
    // The third throttle argument gives this limit its own counter. Without it, Laravel counts every
    // throttle:x,y route for the same user (or guest IP) together.
    Route::post('/requests', [BloodRequestController::class, 'store'])
        ->middleware('throttle:5,10,requests')
        ->name('requests.store');
    Route::get('/requests/{bloodRequest}', [BloodRequestController::class, 'show'])
        ->where('bloodRequest', '[0-9]{1,18}')
        ->name('requests.show');
    Route::patch('/requests/{bloodRequest}/status', [BloodRequestController::class, 'updateStatus'])
        ->where('bloodRequest', '[0-9]{1,18}')
        ->name('requests.status');
    Route::post('/requests/{bloodRequest}/matches', [BloodRequestController::class, 'findDonors'])
        ->where('bloodRequest', '[0-9]{1,18}')
        ->middleware('throttle:5,10,find-donors')
        ->name('requests.matches.store');
    // Design D3: a donor who accepted, and recording their donation (DEC-022).
    Route::get('/requests/{bloodRequest}/donors/{donorMatch}', [RequestDonorController::class, 'show'])
        ->where(['bloodRequest' => '[0-9]{1,18}', 'donorMatch' => '[0-9]{1,18}'])
        ->name('requests.donor');
    Route::post('/requests/{bloodRequest}/donors/{donorMatch}/donation', [RequestDonorController::class, 'storeDonation'])
        ->where(['bloodRequest' => '[0-9]{1,18}', 'donorMatch' => '[0-9]{1,18}'])
        ->middleware('throttle:10,1,record-donation')
        ->name('requests.donor.donation');
});

Route::middleware(['auth', 'verified', 'role:donor'])->group(function () {
    Route::get('/matches', [DonorMatchController::class, 'index'])->name('matches.index');
    Route::patch('/matches/{donorMatch}', [DonorMatchController::class, 'update'])
        ->where('donorMatch', '[0-9]{1,18}')
        ->name('matches.update');
    Route::post('/matches/{donorMatch}/withdraw', [DonorMatchController::class, 'withdraw'])
        ->where('donorMatch', '[0-9]{1,18}')
        ->name('matches.withdraw');
    Route::patch('/availability', AvailabilityController::class)->name('availability.update');
    Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
    Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');
    Route::delete('/donations/{donation}', [DonationController::class, 'destroy'])
        ->where('donation', '[0-9]{1,18}')
        ->name('donations.destroy');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/donors/pending', [DonorVerificationController::class, 'index'])->name('donors.pending');
    Route::post('/donors/{donor}/verify', [DonorVerificationController::class, 'verify'])->name('donors.verify');
    Route::post('/donors/{donor}/reject', [DonorVerificationController::class, 'reject'])->name('donors.reject');
    Route::post('/donors/{donor}/suspend', [DonorVerificationController::class, 'suspend'])->name('donors.suspend');
    Route::post('/donors/{donor}/reinstate', [DonorVerificationController::class, 'reinstate'])->name('donors.reinstate');

    // M2.6 admin dashboard, records, status override, hospitals and reports.
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/donors', [Admin\DonorController::class, 'index'])->name('donors.index');
    Route::get('/donors/{donor}', [Admin\DonorController::class, 'show'])->whereNumber('donor')->name('donors.show');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/requests', [Admin\RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{bloodRequest}', [Admin\RequestController::class, 'show'])
        ->whereNumber('bloodRequest')
        ->name('requests.show');
    Route::patch('/requests/{bloodRequest}/status', [Admin\RequestController::class, 'updateStatus'])
        ->whereNumber('bloodRequest')
        ->name('requests.status');
    Route::resource('hospitals', Admin\HospitalController::class)->except('show');
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/requests.csv', [Admin\ReportController::class, 'requests'])->name('reports.requests');
    Route::get('/reports/donors.csv', [Admin\ReportController::class, 'donors'])->name('reports.donors');
});
