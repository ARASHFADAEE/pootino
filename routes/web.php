<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdReportController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\TelegramWebhookController;
use App\Models\Province;
use Illuminate\Support\Facades\Route;

Route::get('/', [AdController::class, 'index'])->name('ads.index');

Route::middleware('auth')->group(function () {
    Route::get('/ads/create', [AdController::class, 'create'])->name('ads.create');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::get('/ads/{ad}/edit', [AdController::class, 'edit'])->name('ads.edit');
    Route::put('/ads/{ad}', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');
    Route::get('/my-ads', [AdController::class, 'myAds'])->name('ads.my');
    Route::post('/ads/{ad}/report', [AdReportController::class, 'store'])->name('ads.report');
});
Route::get('/ads/{ad}', [AdController::class, 'show'])->name('ads.show');

Route::prefix('auth')->name('auth.otp.')->group(function () {
    Route::get('/phone', [OtpController::class, 'showPhoneForm'])->name('phone');
    Route::post('/send-otp', [OtpController::class, 'sendOtp'])->name('send');
    Route::get('/verify', [OtpController::class, 'showVerifyForm'])->name('verify-form');
    Route::post('/verify', [OtpController::class, 'verifyOtp'])->name('verify');
    Route::post('/resend', [OtpController::class, 'resendOtp'])->name('resend');
    Route::get('/complete-profile', [OtpController::class, 'showCompleteProfileForm'])->middleware('auth')->name('complete-profile-form');
    Route::post('/complete-profile', [OtpController::class, 'completeProfile'])->middleware('auth')->name('complete-profile');
    Route::post('/logout', [OtpController::class, 'logout'])->name('logout');
});

Route::get('/api/cities/{province}', fn (Province $province) => $province->cities()->orderBy('name')->get(['id', 'name']));
Route::post('/telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/ads/{ad}/approve', [AdminController::class, 'approve'])->name('ads.approve');
    Route::post('/ads/{ad}/reject', [AdminController::class, 'reject'])->name('ads.reject');
});



Route::get('/test', function () {
    return view('test');
});