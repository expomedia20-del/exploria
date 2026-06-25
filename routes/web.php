<?php

use App\Http\Controllers\Admin\AdvertisingController;
use App\Http\Controllers\Admin\CampaignRegistryController;
use App\Http\Controllers\Admin\MissionRewardRegistryController;
use App\Http\Controllers\Admin\PartnerRegistryController;
use App\Http\Controllers\Admin\QrRegistryController;
use App\Http\Controllers\Admin\RewardApprovalController;
use App\Http\Controllers\Admin\VenueRegistryController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Display\DisplayAdvertisingController;
use App\Http\Controllers\Hub\HubAdScheduleController;
use App\Http\Controllers\Hub\HubManagerDashboardController;
use App\Http\Controllers\Partner\PartnerAdvertisingController;
use App\Http\Controllers\Partner\PartnerDashboardController;
use App\Http\Controllers\Partner\PartnerOfferController;
use App\Http\Controllers\Partner\RewardRedemptionController;
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
    Route::get('/partner/dashboard', [PartnerDashboardController::class, 'page'])
        ->middleware('role:shop_partner,sponsor')
        ->name('partner.dashboard');
    Route::post('/partner/redemptions/confirm', [RewardRedemptionController::class, 'confirm'])
        ->middleware('role:shop_partner,sponsor')
        ->name('partner.redemptions.confirm');
    Route::post('/partner/offers', [PartnerOfferController::class, 'store'])
        ->middleware('role:shop_partner,sponsor')
        ->name('partner.offers.store');
    Route::get('/partner/ads', [PartnerAdvertisingController::class, 'page'])
        ->middleware('role:shop_partner,sponsor')
        ->name('partner.ads.page');
    Route::post('/partner/ads', [PartnerAdvertisingController::class, 'store'])
        ->middleware('role:shop_partner,sponsor')
        ->name('partner.ads.store');
    Route::get('/hub/dashboard', [HubManagerDashboardController::class, 'page'])
        ->middleware('role:hub_manager')
        ->name('hub.dashboard');
    Route::post('/hub/ads/{adRequest}/schedule', [HubAdScheduleController::class, 'store'])
        ->middleware('role:hub_manager')
        ->name('hub.ads.schedule');
});

Route::get('/api/v1/display/{displayDevice:code}/schedule', [DisplayAdvertisingController::class, 'schedule'])
    ->name('display.schedule');
Route::post('/api/v1/display/{displayDevice:code}/events', [DisplayAdvertisingController::class, 'event'])
    ->middleware('throttle:120,1')
    ->name('display.events.store');
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

Route::get('/admin/ads', [AdvertisingController::class, 'page'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.ads.page');

Route::post('/admin/rewards/{reward}/approve', [RewardApprovalController::class, 'approve'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.approve');

Route::post('/admin/rewards/{reward}/reject', [RewardApprovalController::class, 'reject'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.reject');

Route::post('/admin/ads/{adRequest}/approve', [AdvertisingController::class, 'approve'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.ads.approve');

Route::post('/admin/ads/{adRequest}/reject', [AdvertisingController::class, 'reject'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.ads.reject');

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

Route::get('/api/v1/admin/ads', [AdvertisingController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,viewer,hub_manager'])
    ->name('admin.ads.index');

Route::post('/api/v1/admin/campaigns', [CampaignRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaigns.api.store');

Route::post('/api/v1/admin/rewards/{reward}/approve', [RewardApprovalController::class, 'approve'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.api.approve');

Route::post('/api/v1/admin/rewards/{reward}/reject', [RewardApprovalController::class, 'reject'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.api.reject');

Route::post('/api/v1/admin/ads/{adRequest}/approve', [AdvertisingController::class, 'approve'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.ads.api.approve');

Route::post('/api/v1/admin/ads/{adRequest}/reject', [AdvertisingController::class, 'reject'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.ads.api.reject');

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
Route::get('/api/v1/partner/dashboard', [PartnerDashboardController::class, 'index'])
    ->middleware(['auth', 'role:shop_partner,sponsor'])
    ->name('partner.dashboard.index');
Route::post('/api/v1/partner/redemptions/confirm', [RewardRedemptionController::class, 'confirm'])
    ->middleware(['auth', 'role:shop_partner,sponsor'])
    ->name('partner.redemptions.api.confirm');
Route::post('/api/v1/partner/offers', [PartnerOfferController::class, 'store'])
    ->middleware(['auth', 'role:shop_partner,sponsor'])
    ->name('partner.offers.api.store');
Route::get('/api/v1/partner/ads', [PartnerAdvertisingController::class, 'index'])
    ->middleware(['auth', 'role:shop_partner,sponsor'])
    ->name('partner.ads.index');
Route::post('/api/v1/partner/ads', [PartnerAdvertisingController::class, 'store'])
    ->middleware(['auth', 'role:shop_partner,sponsor'])
    ->name('partner.ads.api.store');
Route::get('/api/v1/hub/dashboard', [HubManagerDashboardController::class, 'index'])
    ->middleware(['auth', 'role:hub_manager'])
    ->name('hub.dashboard.index');
Route::post('/api/v1/hub/ads/{adRequest}/schedule', [HubAdScheduleController::class, 'store'])
    ->middleware(['auth', 'role:hub_manager'])
    ->name('hub.ads.api.schedule');

Route::middleware(['auth', 'verified'])->get('dashboard', DashboardController::class)->name('dashboard');

require __DIR__.'/settings.php';
