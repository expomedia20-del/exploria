<?php

namespace Tests\Feature\Management;

use Tests\TestCase;

class CommercialAccessWorkflowUxTest extends TestCase
{
    public function test_shared_workflow_navigation_exposes_position_and_next_action(): void
    {
        $navigation = file_get_contents(
            resource_path('js/components/dashboard/workflow-page-navigation.tsx'),
        );

        $this->assertIsString($navigation);
        $this->assertStringContainsString('aria-current={isActive', $navigation);
        $this->assertStringContainsString('پس از تکمیل این بخش', $navigation);
        $this->assertStringContainsString('گردش‌کار واحد تجاری و اسپانسر', $navigation);
        $this->assertStringContainsString('گردش‌کار نقش و دسترسی', $navigation);
        $this->assertStringContainsString('مسیر کاربر اکسپلوریا', $navigation);
        $this->assertStringContainsString('sm:grid-cols-2', $navigation);
    }

    public function test_commercial_and_access_pages_share_one_consistent_workflow(): void
    {
        $commercialPages = [
            resource_path('js/pages/admin/partners/index.tsx'),
            resource_path('js/pages/admin/campaign-participants/index.tsx'),
            resource_path('js/pages/admin/sponsors/index.tsx'),
            resource_path('js/pages/admin/ads/index.tsx'),
        ];

        foreach ($commercialPages as $page) {
            $content = file_get_contents($page);

            $this->assertIsString($content);
            $this->assertStringContainsString('workflow="commercial"', $content, $page);
        }

        $accessPages = [
            resource_path('js/pages/admin/role-operations/index.tsx'),
            resource_path('js/pages/admin/users/index.tsx'),
            resource_path('js/pages/admin/access-scopes/index.tsx'),
            resource_path('js/pages/admin/users/guide.tsx'),
        ];

        foreach ($accessPages as $page) {
            $content = file_get_contents($page);

            $this->assertIsString($content);
            $this->assertStringContainsString('workflow="access"', $content, $page);
        }
    }

    public function test_long_operational_lists_use_progressive_disclosure(): void
    {
        $roleOperations = file_get_contents(
            resource_path('js/pages/admin/role-operations/index.tsx'),
        );
        $users = file_get_contents(resource_path('js/pages/admin/users/index.tsx'));
        $adminAds = file_get_contents(resource_path('js/pages/admin/ads/index.tsx'));
        $partnerAds = file_get_contents(resource_path('js/pages/partner/ads.tsx'));
        $accessScopes = file_get_contents(
            resource_path('js/pages/admin/access-scopes/index.tsx'),
        );

        $this->assertIsString($roleOperations);
        $this->assertIsString($users);
        $this->assertIsString($adminAds);
        $this->assertIsString($partnerAds);
        $this->assertIsString($accessScopes);
        $this->assertStringContainsString('<details className="group', $roleOperations);
        $this->assertStringContainsString('<details', $users);
        $this->assertStringContainsString('<details', $adminAds);
        $this->assertStringContainsString('<details', $partnerAds);
        $this->assertStringContainsString('ثبت دسترسی خاص یا موقت', $accessScopes);
    }

    public function test_storefront_keeps_public_ads_distinct_from_rewarded_game_content(): void
    {
        $storefront = file_get_contents(resource_path('js/pages/offers/index.tsx'));

        $this->assertIsString($storefront);
        $this->assertStringContainsString('workflow="participant"', $storefront);
        $this->assertStringContainsString('ویترین عمومی، بدون امتیاز', $storefront);
        $this->assertStringContainsString(
            'پیشنهادهای امتیازآور هر مرحله فقط داخل همان مسیر',
            $storefront,
        );
        $this->assertStringContainsString("ad.creativeType === 'image'", $storefront);
    }
}
