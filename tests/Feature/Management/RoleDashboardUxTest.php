<?php

namespace Tests\Feature\Management;

use Tests\TestCase;

class RoleDashboardUxTest extends TestCase
{
    public function test_role_dashboard_navigation_is_accessible_and_mobile_first(): void
    {
        $navigation = file_get_contents(
            resource_path('js/components/dashboard/role-dashboard-navigation.tsx'),
        );

        $this->assertIsString($navigation);
        $this->assertStringContainsString('aria-label="بخش‌های پنل مدیریتی"', $navigation);
        $this->assertStringContainsString("aria-current={isActive ? 'page' : undefined}", $navigation);
        $this->assertStringContainsString('grid-cols-2', $navigation);
        $this->assertStringContainsString('onSelect(item.key)', $navigation);
    }

    public function test_hub_dashboard_prioritizes_the_next_action_and_separates_work_areas(): void
    {
        $dashboard = file_get_contents(resource_path('js/pages/hub/dashboard.tsx'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString('کار بعدی شما', $dashboard);
        $this->assertStringContainsString("key: 'scope'", $dashboard);
        $this->assertStringContainsString("key: 'displays'", $dashboard);
        $this->assertStringContainsString("key: 'reviews'", $dashboard);
        $this->assertStringContainsString("window.history.replaceState(null, '', `#\${section}`)", $dashboard);
        $this->assertStringContainsString("activeSection === 'reviews'", $dashboard);
    }

    public function test_venue_dashboard_exposes_management_priority_without_one_long_page(): void
    {
        $dashboard = file_get_contents(resource_path('js/pages/venue/dashboard.tsx'));

        $this->assertIsString($dashboard);
        $this->assertStringContainsString('اولویت مدیریتی امروز', $dashboard);
        $this->assertStringContainsString("key: 'campaigns'", $dashboard);
        $this->assertStringContainsString("key: 'network'", $dashboard);
        $this->assertStringContainsString("key: 'media'", $dashboard);
        $this->assertStringContainsString("key: 'rewards'", $dashboard);
        $this->assertStringContainsString("activeSection === 'overview'", $dashboard);
        $this->assertStringContainsString("activeSection === 'media'", $dashboard);
    }
}
