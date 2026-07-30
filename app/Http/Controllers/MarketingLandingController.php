<?php

namespace App\Http\Controllers;

use App\Actions\Events\RecordDomainEventAction;
use App\Http\Requests\StoreMarketingLeadRequest;
use App\Services\MarketingLeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class MarketingLandingController extends Controller
{
    public function home(): Response
    {
        return $this->render('home');
    }

    public function solution(string $focus): Response
    {
        abort_unless(array_key_exists($focus, $this->seoProfiles()), 404);

        return $this->render($focus);
    }

    public function storeLead(StoreMarketingLeadRequest $request, MarketingLeadService $leads, RecordDomainEventAction $events): RedirectResponse
    {
        $lead = $leads->create($request->validated(), $request);
        $events->execute('marketing.lead_created', $request->user(), $request->session()->getId(), 'marketing_lead', $lead->id, [
            'audience_type' => $lead->audience_type,
            'city' => $lead->city,
            'source_path' => $lead->source_path,
        ]);

        return back()->with('success', 'درخواست شما ثبت شد. تیم اکسپلوریا برای هماهنگی دمو پیگیری می‌کند.');
    }

    public function robots(Request $request): HttpResponse
    {
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        return response("User-agent: *\nAllow: /\nSitemap: {$baseUrl}/sitemap.xml\n", 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(Request $request): HttpResponse
    {
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $urls = collect([
            '/',
            '/solutions/venues',
            '/solutions/commercial-units',
            '/solutions/visitors',
            '/offers',
        ])->map(fn (string $path): string => <<<XML
    <url>
        <loc>{$baseUrl}{$path}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
XML)->implode("\n");

        return response(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$urls}
</urlset>
XML, 200, ['Content-Type' => 'application/xml']);
    }

    private function render(string $focus): Response
    {
        $profiles = $this->seoProfiles();

        return Inertia::render('welcome', [
            'marketingFocus' => $focus,
            'seo' => $profiles[$focus] ?? $profiles['home'],
        ]);
    }

    /** @return array<string, array{title: string, description: string, canonicalPath: string}> */
    private function seoProfiles(): array
    {
        return [
            'home' => [
                'title' => 'اکسپلوریا | پلتفرم کمپین، QR، پاداش و درآمدزایی مکان',
                'description' => 'اکسپلوریا برای مکان‌های گردشگری، مراکز تجاری و اسپانسرها کمپین QR، مأموریت، پاداش، تبلیغات و گزارش فروش‌پذیر می‌سازد.',
                'canonicalPath' => '/',
            ],
            'venues' => [
                'title' => 'اکسپلوریا برای مکان‌های گردشگری و تفریحی',
                'description' => 'طراحی مسیر بازدید، QR، مأموریت، گنج، پاداش، تبلیغات و گزارش اجرایی برای برج‌ها، پارک‌ها، شهربازی‌ها و مجموعه‌های فرهنگی.',
                'canonicalPath' => '/solutions/venues',
            ],
            'commercial-units' => [
                'title' => 'اکسپلوریا برای فروشگاه‌ها، فودکورت و واحدهای تجاری',
                'description' => 'اتصال واحدهای تجاری به کمپین مکان با پیشنهاد، پاداش، مصرف کد، تبلیغات و گزارش مراجعه قابل پیگیری.',
                'canonicalPath' => '/solutions/commercial-units',
            ],
            'visitors' => [
                'title' => 'اکسپلوریا برای جذب و مشارکت بازدیدکننده',
                'description' => 'تجربه QRمحور برای مشارکت بازدیدکننده با مأموریت، امتیاز، گنج، پاداش و پیشنهادهای مرتبط با مسیر حضور.',
                'canonicalPath' => '/solutions/visitors',
            ],
        ];
    }
}
