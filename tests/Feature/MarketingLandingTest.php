<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MarketingLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_includes_seo_payload(): void
    {
        $this->withoutVite();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('welcome')
                ->where('marketingFocus', 'home')
                ->where('seo.canonicalPath', '/')
                ->where('seo.title', 'اکسپلوریا | پلتفرم کمپین، QR، پاداش و درآمدزایی مکان'));

        $this->get(route('marketing.solution', ['focus' => 'venues']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('welcome')
                ->where('marketingFocus', 'venues')
                ->where('seo.canonicalPath', '/solutions/venues'));
    }

    public function test_admin_can_review_marketing_leads_in_inbox_and_update_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $lead = MarketingLead::query()->create([
            'audience_type' => 'venue',
            'organization_name' => 'برج میلاد',
            'contact_name' => 'مدیر فروش',
            'mobile' => '09120000000',
            'city' => 'تهران',
            'project_hint' => 'دموی مسیر مکان',
            'notes' => 'هماهنگی برای جلسه معرفی',
            'status' => 'new',
            'source_path' => '/solutions/venues',
            'metadata' => ['source' => 'public_marketing_landing'],
        ]);

        MarketingLead::query()->create([
            'audience_type' => 'commercial_unit',
            'organization_name' => 'فودکورت نمونه',
            'contact_name' => 'مدیر واحد',
            'mobile' => '09121111111',
            'status' => 'closed',
            'source_path' => '/solutions/commercial-units',
            'metadata' => ['source' => 'public_marketing_landing'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.marketing-leads.page', ['status' => 'new']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/marketing-leads/index')
                ->where('filters.status', 'new')
                ->where('stats.total', 2)
                ->where('stats.new', 1)
                ->has('leads', 1)
                ->where('leads.0.organizationName', 'برج میلاد')
                ->where('leads.0.status', 'new'));

        $this->actingAs($admin)
            ->patch(route('admin.marketing-leads.status.update', $lead), [
                'status' => 'demo_scheduled',
                'internal_notes' => 'جلسه دمو برای هفته آینده هماهنگ شد.',
            ])
            ->assertRedirect();

        $lead->refresh();
        $this->assertSame('demo_scheduled', $lead->status);
        $this->assertSame('جلسه دمو برای هفته آینده هماهنگ شد.', $lead->metadata['internal_notes']);
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.marketing_lead.status_updated',
            'object_type' => 'marketing_lead',
            'object_id' => $lead->id,
        ]);
    }

    public function test_public_marketing_lead_form_stores_request_and_event(): void
    {
        $this->from(route('marketing.solution', ['focus' => 'commercial-units']))
            ->post(route('marketing.leads.store'), [
                'audience_type' => 'commercial_unit',
                'organization_name' => 'فودکورت تست',
                'contact_name' => 'مدیر تست',
                'mobile' => '09120000000',
                'city' => 'تهران',
                'project_hint' => 'فودکورت داخل مجموعه تفریحی',
                'notes' => 'درخواست دمو برای کمپین فروشگاهی',
                'source_path' => '/solutions/commercial-units',
            ])
            ->assertRedirect(route('marketing.solution', ['focus' => 'commercial-units']));

        $lead = MarketingLead::query()->firstOrFail();
        $this->assertSame('commercial_unit', $lead->audience_type);
        $this->assertSame('فودکورت تست', $lead->organization_name);
        $this->assertSame('new', $lead->status);
        $this->assertSame('/solutions/commercial-units', $lead->source_path);
        $this->assertSame('public_marketing_landing', $lead->metadata['source']);

        $this->assertDatabaseHas('event_log', [
            'event_type' => 'marketing.lead_created',
            'object_type' => 'marketing_lead',
            'object_id' => $lead->id,
        ]);
    }

    public function test_marketing_lead_honeypot_blocks_spam_submission(): void
    {
        $this->post(route('marketing.leads.store'), [
            'audience_type' => 'venue',
            'contact_name' => 'Spam',
            'mobile' => '09120000000',
            'company_url' => 'https://example.test',
        ])->assertSessionHasErrors('company_url');

        $this->assertDatabaseCount('marketing_leads', 0);
    }

    public function test_robots_and_sitemap_include_public_seo_routes(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('/sitemap.xml');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/solutions/venues')
            ->assertSee('/solutions/commercial-units')
            ->assertSee('/solutions/visitors');
    }
}
