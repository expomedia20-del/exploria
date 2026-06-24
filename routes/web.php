<?php

use App\Http\Controllers\Admin\CampaignRegistryController;
use App\Http\Controllers\Admin\MissionRewardRegistryController;
use App\Http\Controllers\Admin\PartnerRegistryController;
use App\Http\Controllers\Admin\QrRegistryController;
use App\Http\Controllers\Admin\VenueRegistryController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RewardWalletController;
use App\Http\Controllers\ScanLandingController;
use App\Http\Controllers\VisitExperienceController;
use App\Http\Controllers\VisitMissionController;
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
    Route::post('/visits/{visit}/missions/{mission}/start', [VisitMissionController::class, 'start'])
        ->name('visits.missions.start');
    Route::post('/visits/{visit}/missions/{mission}/complete', [VisitMissionController::class, 'complete'])
        ->name('visits.missions.complete');
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

Route::post('/admin/qr-codes', [QrRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.qr-codes.store');

Route::get('/admin/partners', [PartnerRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.partners.page');

Route::get('/admin/campaigns', [CampaignRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.campaigns.page');

Route::get('/admin/missions', [MissionRewardRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.missions.page');

Route::post('/admin/campaigns', [CampaignRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaigns.store');

Route::get('/admin/venues', [VenueRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.venues.page');

Route::get('/api/v1/admin/qr-codes', [QrRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer'])
    ->name('admin.qr-codes.index');

Route::post('/api/v1/admin/qr-codes', [QrRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.qr-codes.api.store');

Route::get('/api/v1/admin/partners', [PartnerRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.partners.index');

Route::get('/api/v1/admin/campaigns', [CampaignRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.campaigns.index');

Route::get('/api/v1/admin/missions', [MissionRewardRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.missions.index');

Route::post('/api/v1/admin/campaigns', [CampaignRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaigns.api.store');

Route::get('/api/v1/admin/venues', [VenueRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.venues.index');

Route::get('/api/v1/visits/{visit}/missions', [VisitMissionController::class, 'index'])
    ->middleware('auth')
    ->name('visits.missions.index');
Route::post('/api/v1/visits/{visit}/missions/{mission}/start', [VisitMissionController::class, 'start'])
    ->middleware('auth')
    ->name('visits.missions.api.start');
Route::post('/api/v1/visits/{visit}/missions/{mission}/complete', [VisitMissionController::class, 'complete'])
    ->middleware('auth')
    ->name('visits.missions.api.complete');
Route::get('/api/v1/rewards/wallet', RewardWalletController::class)
    ->middleware('auth')
    ->name('rewards.wallet');

Route::middleware(['auth', 'verified'])->get('dashboard', DashboardController::class)->name('dashboard');

require __DIR__.'/settings.php';
