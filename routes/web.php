<?php

use App\Http\Controllers\Admin\AdvertisingController;
use App\Http\Controllers\Admin\CampaignBuilderController;
use App\Http\Controllers\Admin\CampaignOperationsController;
use App\Http\Controllers\Admin\CampaignParticipantController;
use App\Http\Controllers\Admin\CampaignRegistryController;
use App\Http\Controllers\Admin\CommercializationController;
use App\Http\Controllers\Admin\DemoCycleController;
use App\Http\Controllers\Admin\DisplayOperationsController;
use App\Http\Controllers\Admin\FinanceWalletController;
use App\Http\Controllers\Admin\InternalOperationsController;
use App\Http\Controllers\Admin\MissionRewardBlueprintController;
use App\Http\Controllers\Admin\MissionRewardRegistryController;
use App\Http\Controllers\Admin\PartnerRegistryController;
use App\Http\Controllers\Admin\QrRegistryController;
use App\Http\Controllers\Admin\RewardApprovalController;
use App\Http\Controllers\Admin\RoleOperationsController;
use App\Http\Controllers\Admin\ScanEventController;
use App\Http\Controllers\Admin\SponsorActivationController;
use App\Http\Controllers\Admin\SupportCenterController;
use App\Http\Controllers\Admin\UserAccessScopeController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserManagementGuideController;
use App\Http\Controllers\Admin\VenueRegistryController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Display\DisplayAdvertisingController;
use App\Http\Controllers\Games\EcoParkTreasureGameController;
use App\Http\Controllers\Hub\HubAdScheduleController;
use App\Http\Controllers\Hub\HubManagerDashboardController;
use App\Http\Controllers\ParticipantDashboardController;
use App\Http\Controllers\Partner\PartnerAdvertisingController;
use App\Http\Controllers\Partner\PartnerDashboardController;
use App\Http\Controllers\Partner\PartnerOfferController;
use App\Http\Controllers\Partner\RewardRedemptionController;
use App\Http\Controllers\RewardWalletController;
use App\Http\Controllers\ScanLandingController;
use App\Http\Controllers\SmartOffersController;
use App\Http\Controllers\Sponsor\SponsorDashboardController;
use App\Http\Controllers\Venue\VenueManagerDashboardController;
use App\Http\Controllers\VisitExperienceController;
use App\Http\Controllers\VisitMissionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');
Route::get('/health', fn () => response('Application up'))->name('health');
Route::inertia('/board', 'demo/board')->name('demo.board');
Route::inertia('/demo', 'welcome')->name('demo');
Route::inertia('/demo/ecosystem', 'demo/ecosystem')->name('demo.ecosystem');
Route::inertia('/demo/missions', 'demo/missions')->name('demo.missions');
Route::inertia('/demo/proposal', 'demo/proposal')->name('demo.proposal');
Route::get('/offers', [SmartOffersController::class, 'page'])->name('offers.page');
Route::get('/games/ecopark-treasure', EcoParkTreasureGameController::class)->name('games.ecopark-treasure');
Route::get('/scan/{code}', ScanLandingController::class)->where('code', '[A-Za-z0-9-]+')->name('scan.landing');
Route::inertia('/access', 'auth/otp')->name('visitor.otp');
Route::middleware('auth')->group(function () {
    Route::inertia('/consent', 'consent')->name('visitor.consent');
    Route::get('/visits/{visit}', VisitExperienceController::class)->name('visits.show');
    Route::post('/visits/{visit}/missions/{mission}/start', [VisitMissionController::class, 'start'])
        ->name('visits.missions.start');
    Route::post('/visits/{visit}/missions/{mission}/complete', [VisitMissionController::class, 'complete'])
        ->name('visits.missions.complete');
    Route::get('/participant/dashboard', ParticipantDashboardController::class)
        ->name('participant.dashboard');
    Route::post('/participant/participation', [ParticipantDashboardController::class, 'startParticipation'])
        ->middleware('role:visitor')
        ->name('participant.participation.start');
    Route::get('/partner/dashboard', [PartnerDashboardController::class, 'page'])
        ->middleware('role:admin,shop_partner')
        ->name('partner.dashboard');
    Route::post('/partner/redemptions/confirm', [RewardRedemptionController::class, 'confirm'])
        ->middleware('role:admin,shop_partner')
        ->name('partner.redemptions.confirm');
    Route::patch('/partner/profile', [PartnerDashboardController::class, 'update'])
        ->middleware('role:admin,shop_partner')
        ->name('partner.profile.update');
    Route::post('/partner/offers', [PartnerOfferController::class, 'store'])
        ->middleware('role:admin,shop_partner')
        ->name('partner.offers.store');
    Route::patch('/partner/offers/{reward}', [PartnerOfferController::class, 'update'])
        ->middleware('role:admin,shop_partner')
        ->name('partner.offers.update');
    Route::get('/partner/ads', [PartnerAdvertisingController::class, 'page'])
        ->middleware('role:admin,shop_partner')
        ->name('partner.ads.page');
    Route::post('/partner/ads', [PartnerAdvertisingController::class, 'store'])
        ->middleware('role:admin,shop_partner')
        ->name('partner.ads.store');
    Route::get('/sponsor/dashboard', [SponsorDashboardController::class, 'page'])
        ->middleware('role:admin,operator,sponsor')
        ->name('sponsor.dashboard');
    Route::post('/sponsor/proposals', [SponsorDashboardController::class, 'storeProposal'])
        ->middleware('role:admin,operator,sponsor')
        ->name('sponsor.proposals.store');
    Route::post('/sponsor/ads', [SponsorDashboardController::class, 'storeAdRequest'])
        ->middleware('role:admin,operator,sponsor')
        ->name('sponsor.ads.store');
    Route::patch('/sponsor/proposals/{proposal}', [SponsorDashboardController::class, 'updateProposal'])
        ->middleware('role:admin,operator,sponsor')
        ->name('sponsor.proposals.update');
    Route::get('/hub/dashboard', [HubManagerDashboardController::class, 'page'])
        ->middleware('role:admin,hub_manager')
        ->name('hub.dashboard');
    Route::get('/ravaq/dashboard', [HubManagerDashboardController::class, 'page'])
        ->middleware('role:admin,hub_manager')
        ->name('ravaq.dashboard');
    Route::get('/venue/dashboard', [VenueManagerDashboardController::class, 'page'])
        ->middleware('role:admin,regional_admin,operator,viewer')
        ->name('venue.dashboard');
    Route::post('/hub/ads/{adRequest}/schedule', [HubAdScheduleController::class, 'store'])
        ->middleware('role:admin,hub_manager')
        ->name('hub.ads.schedule');
    Route::post('/hub/ad-placements/{adPlacement}/cancel', [HubAdScheduleController::class, 'cancel'])
        ->middleware('role:admin,hub_manager')
        ->name('hub.ad-placements.cancel');
});

Route::get('/api/v1/display/{displayDevice:code}/schedule', [DisplayAdvertisingController::class, 'schedule'])
    ->name('display.schedule');
Route::post('/api/v1/display/{displayDevice:code}/events', [DisplayAdvertisingController::class, 'event'])
    ->middleware('throttle:120,1')
    ->name('display.events.store');
Route::post('/api/v1/display/{displayDevice:code}/heartbeat', [DisplayAdvertisingController::class, 'heartbeat'])
    ->middleware('throttle:120,1')
    ->name('display.heartbeat.store');
Route::get('/api/v1/offers', [SmartOffersController::class, 'index'])->name('offers.index');
Route::post('/api/v1/offers/game-events', [SmartOffersController::class, 'storeGameEvent'])
    ->middleware('throttle:120,1')
    ->name('offers.game-events.store');
Route::prefix('api/v1/auth/otp')->middleware('throttle:5,1')->group(function () {
    Route::post('request', [OtpController::class, 'request'])->name('otp.request');
    Route::post('verify', [OtpController::class, 'verify'])->name('otp.verify');
});

Route::prefix('api/v1/consents')->middleware('throttle:30,1')->group(function () {
    Route::get('current', [ConsentController::class, 'current'])->name('consents.current');
    Route::post('accept', [ConsentController::class, 'accept'])->middleware('auth')->name('consents.accept');
});

Route::get('/api/v1/sponsor/dashboard', [SponsorDashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,operator,sponsor'])
    ->name('sponsor.dashboard.index');
Route::get('/api/v1/venue/dashboard', [VenueManagerDashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('venue.dashboard.index');
Route::get('/api/v1/ravaq/dashboard', [HubManagerDashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,hub_manager'])
    ->name('ravaq.dashboard.index');
Route::patch('/api/v1/sponsor/proposals/{proposal}', [SponsorDashboardController::class, 'updateProposal'])
    ->middleware(['auth', 'role:admin,operator,sponsor'])
    ->name('sponsor.proposals.api.update');
Route::post('/api/v1/sponsor/ads', [SponsorDashboardController::class, 'storeAdRequest'])
    ->middleware(['auth', 'role:admin,operator,sponsor'])
    ->name('sponsor.ads.api.store');

Route::get('/admin/qr-codes', [QrRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.qr-codes.page');
Route::get('/admin/events/scan-log', [ScanEventController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.events.scan-log.page');

Route::get('/admin/internal-operations', [InternalOperationsController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.internal-operations.page');

Route::get('/admin/demo-cycle', [DemoCycleController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.demo-cycle.page');
Route::post('/admin/demo-cycle/checklist', [DemoCycleController::class, 'updateChecklistItem'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.demo-cycle.checklist.update');
Route::post('/admin/demo-cycle/run-stress-demo', [DemoCycleController::class, 'runStressDemo'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.demo-cycle.run-stress-demo');

Route::get('/admin/commercialization', [CommercializationController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.commercialization.page');

Route::get('/admin/finance-wallets', [FinanceWalletController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.finance-wallets.page');
Route::post('/admin/finance-wallets/ledger', [FinanceWalletController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.finance-wallets.ledger.store');

Route::get('/admin/support', [SupportCenterController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager,shop_partner,sponsor,visitor'])
    ->name('admin.support.page');

Route::get('/admin/users', [UserManagementController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.users.page');
Route::get('/admin/users/guide', [UserManagementGuideController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.users.guide');
Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.users.role');
Route::post('/admin/users/{user}/deactivate-access', [UserManagementController::class, 'deactivateAccess'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.users.deactivate-access');
Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.users.destroy');

Route::post('/admin/qr-codes', [QrRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.qr-codes.store');
Route::delete('/admin/qr-codes/{qrCode}', [QrRegistryController::class, 'destroy'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.qr-codes.destroy');

Route::get('/admin/partners', [PartnerRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.partners.page');

Route::get('/admin/campaigns', [CampaignRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.campaigns.page');

Route::get('/admin/campaign-builder', [CampaignBuilderController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.campaign-builder.page');
Route::post('/admin/campaign-builder/{campaign}/activate', [CampaignBuilderController::class, 'activate'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-builder.activate');

Route::get('/admin/campaign-operations', [CampaignOperationsController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.campaign-operations.page');
Route::post('/admin/campaign-operations/review', [CampaignOperationsController::class, 'review'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-operations.review');
Route::delete('/admin/campaign-operations/review', [CampaignOperationsController::class, 'resetReview'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-operations.review.destroy');

Route::get('/admin/campaign-participants', [CampaignParticipantController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.campaign-participants.page');
Route::post('/admin/campaign-participants', [CampaignParticipantController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-participants.store');
Route::delete('/admin/campaign-participants/{participant}', [CampaignParticipantController::class, 'destroy'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-participants.destroy');

Route::get('/admin/sponsors', [SponsorActivationController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.sponsors.page');
Route::post('/admin/sponsors', [SponsorActivationController::class, 'storeSponsor'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsors.store');
Route::post('/admin/campaign-sponsorships', [SponsorActivationController::class, 'storeSponsorship'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-sponsorships.store');
Route::post('/admin/sponsor-partner-assignments', [SponsorActivationController::class, 'storePartnerAssignment'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsor-partner-assignments.store');
Route::post('/admin/sponsor-proposals/{proposal}/status', [SponsorActivationController::class, 'updateProposalStatus'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsor-proposals.status');
Route::post('/admin/sponsor-proposals/{proposal}/activate', [SponsorActivationController::class, 'activateProposal'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsor-proposals.activate');

Route::get('/admin/mission-blueprints', [MissionRewardBlueprintController::class, 'page'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.mission-blueprints.page');
Route::get('/admin/missions', [MissionRewardRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.missions.page');
Route::post('/admin/missions', [MissionRewardRegistryController::class, 'storeMission'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.missions.store');
Route::delete('/admin/missions/{mission}', [MissionRewardRegistryController::class, 'destroyMission'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.missions.destroy');
Route::post('/admin/rewards', [MissionRewardRegistryController::class, 'storeReward'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.rewards.store');
Route::post('/admin/rewards/{reward}/sponsor-assignment', [MissionRewardRegistryController::class, 'assignSponsorIncentive'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.rewards.sponsor-assignment');
Route::delete('/admin/rewards/{reward}', [MissionRewardRegistryController::class, 'destroyReward'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.rewards.destroy');
Route::post('/admin/treasures', [MissionRewardRegistryController::class, 'storeTreasure'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.treasures.store');
Route::delete('/admin/treasures/{treasure}', [MissionRewardRegistryController::class, 'destroyTreasure'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.treasures.destroy');

Route::get('/admin/ads', [AdvertisingController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.ads.page');

Route::post('/admin/rewards/{reward}/approve', [RewardApprovalController::class, 'approve'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.approve');

Route::post('/admin/rewards/{reward}/reject', [RewardApprovalController::class, 'reject'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.reject');

Route::post('/admin/rewards/{reward}/revision', [RewardApprovalController::class, 'requestRevision'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.revision');

Route::post('/admin/ads/{adRequest}/approve', [AdvertisingController::class, 'approve'])
    ->middleware(['auth', 'role:admin,regional_admin,operator'])
    ->name('admin.ads.approve');

Route::post('/admin/ads/{adRequest}/reject', [AdvertisingController::class, 'reject'])
    ->middleware(['auth', 'role:admin,regional_admin,operator'])
    ->name('admin.ads.reject');

Route::post('/admin/campaigns', [CampaignRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaigns.store');
Route::delete('/admin/campaigns/{campaign}', [CampaignRegistryController::class, 'destroy'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaigns.destroy');

Route::get('/admin/display-operations', [DisplayOperationsController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator'])
    ->name('admin.display-operations.page');

Route::get('/admin/role-operations', [RoleOperationsController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.role-operations.page');

Route::get('/admin/access-scopes', [UserAccessScopeController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.access-scopes.page');

Route::post('/admin/access-scopes', [UserAccessScopeController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.access-scopes.store');

Route::post('/admin/access-scopes/accounts', [UserAccessScopeController::class, 'storeAccount'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.access-scopes.accounts.store');

Route::post('/admin/access-scopes/{accessScope}/deactivate', [UserAccessScopeController::class, 'deactivate'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.access-scopes.deactivate');

Route::post('/admin/display-operations/placements/{adPlacement}/schedule', [DisplayOperationsController::class, 'schedule'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.display-operations.placements.schedule');

Route::post('/admin/display-operations/placements/{adPlacement}/cancel', [DisplayOperationsController::class, 'cancel'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.display-operations.placements.cancel');
Route::get('/admin/venues', [VenueRegistryController::class, 'page'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.venues.page');
Route::get('/admin/venues/facilities-template', [VenueRegistryController::class, 'facilitiesTemplate'])
    ->middleware(['auth', 'role:admin,regional_admin,operator'])
    ->name('admin.venues.facilities-template');
Route::patch('/admin/venues/{venue}/profile', [VenueRegistryController::class, 'updateProfile'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.venues.profile.update');

Route::get('/api/v1/admin/qr-codes', [QrRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.qr-codes.index');
Route::get('/api/v1/admin/events/scan-log', [ScanEventController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer'])
    ->name('admin.events.scan-log.index');

Route::post('/api/v1/admin/qr-codes', [QrRegistryController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.qr-codes.api.store');

Route::get('/api/v1/admin/partners', [PartnerRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.partners.index');

Route::get('/api/v1/admin/campaigns', [CampaignRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.campaigns.index');

Route::get('/api/v1/admin/campaign-operations', [CampaignOperationsController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.campaign-operations.index');
Route::post('/api/v1/admin/campaign-operations/review', [CampaignOperationsController::class, 'review'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-operations.api.review');

Route::get('/api/v1/admin/campaign-participants', [CampaignParticipantController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.campaign-participants.index');
Route::post('/api/v1/admin/campaign-participants', [CampaignParticipantController::class, 'store'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-participants.api.store');

Route::get('/api/v1/admin/sponsors', [SponsorActivationController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.sponsors.index');
Route::post('/api/v1/admin/sponsors', [SponsorActivationController::class, 'storeSponsor'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsors.api.store');
Route::post('/api/v1/admin/campaign-sponsorships', [SponsorActivationController::class, 'storeSponsorship'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.campaign-sponsorships.api.store');
Route::post('/api/v1/admin/sponsor-partner-assignments', [SponsorActivationController::class, 'storePartnerAssignment'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsor-partner-assignments.api.store');
Route::post('/api/v1/admin/sponsor-proposals/{proposal}/status', [SponsorActivationController::class, 'updateProposalStatus'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsor-proposals.api.status');
Route::post('/api/v1/admin/sponsor-proposals/{proposal}/activate', [SponsorActivationController::class, 'activateProposal'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.sponsor-proposals.api.activate');

Route::get('/api/v1/admin/mission-blueprints', [MissionRewardBlueprintController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.mission-blueprints.index');
Route::get('/api/v1/admin/missions', [MissionRewardRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.missions.index');
Route::post('/api/v1/admin/missions', [MissionRewardRegistryController::class, 'storeMission'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.missions.api.store');
Route::post('/api/v1/admin/rewards', [MissionRewardRegistryController::class, 'storeReward'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.rewards.api.store');
Route::post('/api/v1/admin/rewards/{reward}/sponsor-assignment', [MissionRewardRegistryController::class, 'assignSponsorIncentive'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.rewards.api.sponsor-assignment');
Route::post('/api/v1/admin/treasures', [MissionRewardRegistryController::class, 'storeTreasure'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.treasures.api.store');
Route::delete('/api/v1/admin/treasures/{treasure}', [MissionRewardRegistryController::class, 'destroyTreasure'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.treasures.api.destroy');

Route::get('/api/v1/admin/ads', [AdvertisingController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
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

Route::post('/api/v1/admin/rewards/{reward}/revision', [RewardApprovalController::class, 'requestRevision'])
    ->middleware(['auth', 'role:admin,operator,hub_manager'])
    ->name('admin.rewards.api.revision');

Route::post('/api/v1/admin/ads/{adRequest}/approve', [AdvertisingController::class, 'approve'])
    ->middleware(['auth', 'role:admin,regional_admin,operator'])
    ->name('admin.ads.api.approve');

Route::post('/api/v1/admin/ads/{adRequest}/reject', [AdvertisingController::class, 'reject'])
    ->middleware(['auth', 'role:admin,regional_admin,operator'])
    ->name('admin.ads.api.reject');

Route::get('/api/v1/admin/display-operations', [DisplayOperationsController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator'])
    ->name('admin.display-operations.index');

Route::post('/api/v1/admin/display-operations/placements/{adPlacement}/schedule', [DisplayOperationsController::class, 'schedule'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.display-operations.placements.api.schedule');

Route::post('/api/v1/admin/display-operations/placements/{adPlacement}/cancel', [DisplayOperationsController::class, 'cancel'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.display-operations.placements.api.cancel');
Route::get('/api/v1/admin/venues', [VenueRegistryController::class, 'index'])
    ->middleware(['auth', 'role:admin,regional_admin,operator,viewer,hub_manager'])
    ->name('admin.venues.index');
Route::patch('/api/v1/admin/venues/{venue}/profile', [VenueRegistryController::class, 'updateProfile'])
    ->middleware(['auth', 'role:admin,operator'])
    ->name('admin.venues.profile.api.update');

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
    ->middleware(['auth', 'role:admin,shop_partner'])
    ->name('partner.dashboard.index');
Route::post('/api/v1/partner/redemptions/confirm', [RewardRedemptionController::class, 'confirm'])
    ->middleware(['auth', 'role:admin,shop_partner'])
    ->name('partner.redemptions.api.confirm');
Route::patch('/api/v1/partner/profile', [PartnerDashboardController::class, 'update'])
    ->middleware(['auth', 'role:admin,shop_partner'])
    ->name('partner.profile.api.update');
Route::post('/api/v1/partner/offers', [PartnerOfferController::class, 'store'])
    ->middleware(['auth', 'role:admin,shop_partner'])
    ->name('partner.offers.api.store');
Route::patch('/api/v1/partner/offers/{reward}', [PartnerOfferController::class, 'update'])
    ->middleware(['auth', 'role:admin,shop_partner'])
    ->name('partner.offers.api.update');
Route::get('/api/v1/partner/ads', [PartnerAdvertisingController::class, 'index'])
    ->middleware(['auth', 'role:admin,shop_partner'])
    ->name('partner.ads.index');
Route::post('/api/v1/partner/ads', [PartnerAdvertisingController::class, 'store'])
    ->middleware(['auth', 'role:admin,shop_partner'])
    ->name('partner.ads.api.store');
Route::get('/api/v1/hub/dashboard', [HubManagerDashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,hub_manager'])
    ->name('hub.dashboard.index');
Route::post('/api/v1/hub/ads/{adRequest}/schedule', [HubAdScheduleController::class, 'store'])
    ->middleware(['auth', 'role:admin,hub_manager'])
    ->name('hub.ads.api.schedule');
Route::post('/api/v1/hub/ad-placements/{adPlacement}/cancel', [HubAdScheduleController::class, 'cancel'])
    ->middleware(['auth', 'role:admin,hub_manager'])
    ->name('hub.ad-placements.api.cancel');

Route::middleware(['auth', 'verified'])->get('dashboard', DashboardController::class)->name('dashboard');

require __DIR__.'/settings.php';
