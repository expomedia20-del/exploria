<?php

namespace Tests\Feature\Partner;

use Tests\TestCase;

class PartnerDashboardUxTest extends TestCase
{
    public function test_partner_dashboard_is_a_sectioned_control_center(): void
    {
        $dashboard = file_get_contents(resource_path('js/pages/partner/dashboard.tsx'));
        $navigation = file_get_contents(resource_path('js/components/partner/partner-dashboard-navigation.tsx'));
        $overview = file_get_contents(resource_path('js/components/partner/partner-dashboard-overview.tsx'));

        $this->assertIsString($dashboard);
        $this->assertIsString($navigation);
        $this->assertIsString($overview);
        $this->assertStringContainsString('PartnerDashboardNavigation', $dashboard);
        $this->assertStringContainsString("'overview'", $dashboard);
        $this->assertStringContainsString("'profile'", $dashboard);
        $this->assertStringContainsString("'offers'", $dashboard);
        $this->assertStringContainsString("'rewards'", $dashboard);
        $this->assertStringContainsString("'redemptions'", $dashboard);
        $this->assertStringContainsString('مرکز کنترل فروشگاه', $overview);
        $this->assertStringContainsString('امروز چه کاری مهم‌تر است؟', $overview);
        $this->assertStringContainsString('مدیریت تبلیغات', $overview);
        $this->assertStringContainsString('/partner/ads', $overview);
    }

    public function test_partner_offer_and_fulfilment_workflows_are_progressive(): void
    {
        $offerWizard = file_get_contents(resource_path('js/components/partner/partner-offer-wizard.tsx'));
        $rewards = file_get_contents(resource_path('js/components/partner/partner-rewards-panel.tsx'));
        $redemptions = file_get_contents(resource_path('js/components/partner/partner-redemptions-panel.tsx'));

        $this->assertIsString($offerWizard);
        $this->assertIsString($rewards);
        $this->assertIsString($redemptions);
        $this->assertStringContainsString('ساخت پیشنهاد در ۴ گام', $offerWizard);
        $this->assertStringContainsString('انتخاب گام کمپین', $offerWizard);
        $this->assertStringContainsString('بازبینی و ارسال', $offerWizard);
        $this->assertStringContainsString('پیگیری پیشنهادهای من', $offerWizard);
        $this->assertStringContainsString('<details', $rewards);
        $this->assertStringContainsString('پاداش و موجودی فروشگاه', $rewards);
        $this->assertStringContainsString('تحویل پاداش به مشتری', $redemptions);
        $this->assertStringContainsString('سابقه مصرف پاداش‌ها', $redemptions);
    }
}
