<?php

use App\Http\Controllers\Admin\PartnerRegistryController;
use App\Http\Controllers\Admin\QrRegistryController;
use App\Http\Controllers\Admin\VenueRegistryController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScanLandingController;
use App\Http\Controllers\VisitExperienceController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::inertia('/board', 'demo/board')->name('demo.board');
Route::inertia('/demo', 'welcome')->name('demo');
Route::inertia('/demo/ecosystem', 'demo/ecosystem')->name('demo.ecosystem');
Route::inertia('/demo/missions', 'demo/missions')->name('demo.missions');
Route::inertia('/demo/proposal', 'demo/proposal')->name('demo.proposal');
Route::get('/scan/{code}', ScanLandingController::class)->where('code', '[A-Za-z0-9-]+')->name('scan.landing');
Route::inertia('/access', 'auth/otp')->name('visitor.otp');
Route::middleware('auth')->group(function () {
    Route::inertia('/consent', 'consent')->name('visitor.consent');
    Route::get('/visits/{visit}', VisitExperienceController::class)->name('visits.show');
});

Route::prefix('api/v1/auth/otp')->middleware('throttle:5,1')->group(function () {
    Route::post('request', [OtpController::class, 'request'])->name('otp.request');
    Route::post('verify', [OtpController::class, 'verify'])->name('otp.verify');
});

Route::prefix('api/v1/consents')->middleware('throttle:30,1')->group(function () {
    Route::get('current', [ConsentController::class, 'current'])->name('consents.current');
    Route::post('accept', [ConsentController::class, 'accept'])->middleware('auth')->name('consents.accept');
});

Route::get('/admin/qr-codes', [QrRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer'])
    ->name('admin.qr-codes.page');

Route::get('/admin/partners', [PartnerRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.partners.page');

Route::get('/admin/venues', [VenueRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.venues.page');

Route::get('/api/v1/admin/qr-codes', [QrRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer'])
    ->name('admin.qr-codes.index');

Route::get('/api/v1/admin/partners', [PartnerRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.partners.index');

Route::get('/api/v1/admin/venues', [VenueRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.venues.index');

Route::middleware(['auth', 'verified'])->get('dashboard', DashboardController::class)->name('dashboard');

require __DIR__.'/settings.php';
