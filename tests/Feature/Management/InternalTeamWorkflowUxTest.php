<?php

namespace Tests\Feature\Management;

use Tests\TestCase;

class InternalTeamWorkflowUxTest extends TestCase
{
    public function test_internal_team_navigation_groups_the_operational_journey(): void
    {
        $navigation = file_get_contents(
            resource_path('js/components/dashboard/internal-team-navigation.tsx'),
        );

        $this->assertIsString($navigation);
        $this->assertStringContainsString('نقشه عملیات تیم داخلی اکسپلوریا', $navigation);
        $this->assertStringContainsString('گام پیشنهادی بعدی', $navigation);
        $this->assertStringContainsString('فرماندهی و آمادگی', $navigation);
        $this->assertStringContainsString('طراحی و ثبت', $navigation);
        $this->assertStringContainsString('اجرا و مسیر', $navigation);
        $this->assertStringContainsString('پایش و رسانه', $navigation);
        $this->assertStringContainsString('فروش و اقتصاد', $navigation);
        $this->assertStringContainsString("aria-current={isActive ? 'page'", $navigation);
        $this->assertStringContainsString('grid-cols-2', $navigation);
    }

    public function test_every_internal_team_page_exposes_the_shared_journey(): void
    {
        $pages = [
            'admin/venues/index.tsx',
            'admin/internal-operations/index.tsx',
            'admin/demo-cycle/index.tsx',
            'admin/commercialization/index.tsx',
            'admin/mission-blueprints/index.tsx',
            'admin/campaigns/index.tsx',
            'admin/campaign-builder/index.tsx',
            'admin/missions/index.tsx',
            'admin/qr-codes/index.tsx',
            'admin/events/index.tsx',
            'admin/campaign-operations/index.tsx',
            'admin/display-operations/index.tsx',
            'admin/finance-wallets/index.tsx',
        ];

        foreach ($pages as $page) {
            $content = file_get_contents(resource_path("js/pages/{$page}"));

            $this->assertIsString($content);
            $this->assertStringContainsString(
                '<InternalTeamNavigation activeHref=',
                $content,
                $page,
            );
        }
    }

    public function test_heavy_internal_pages_use_progressive_disclosure_and_bounded_events(): void
    {
        $venues = file_get_contents(resource_path('js/pages/admin/venues/index.tsx'));
        $blueprints = file_get_contents(
            resource_path('js/pages/admin/mission-blueprints/index.tsx'),
        );
        $demoCycle = file_get_contents(
            resource_path('js/pages/admin/demo-cycle/index.tsx'),
        );
        $events = file_get_contents(resource_path('js/pages/admin/events/index.tsx'));

        $this->assertIsString($venues);
        $this->assertIsString($blueprints);
        $this->assertIsString($demoCycle);
        $this->assertIsString($events);
        $this->assertStringContainsString('مشاهده ارزیابی، آمادگی دمو و ساختار', $venues);
        $this->assertStringContainsString('<details', $blueprints);
        $this->assertStringContainsString('group-open:rotate-180', $demoCycle);
        $this->assertStringContainsString('useState(25)', $events);
        $this->assertStringContainsString('نمایش ۲۵ رویداد بعدی', $events);
    }
}
