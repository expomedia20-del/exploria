import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgePercent,
    CalendarClock,
    Gift,
    Megaphone,
    ShieldCheck,
    Sparkles,
    Store,
    TicketCheck,
} from 'lucide-react';

type SmartAd = {
    id: string;
    code: string;
    title: string;
    bodyCopy: string | null;
    ctaText: string;
    targetUrl: string | null;
    adType: string;
    creativeType: string | null;
    assetUrl: string | null;
    placementType: string | null;
    venueName: string | null;
    partnerName: string | null;
    partnerType: string | null;
    startsAt: string | null;
    endsAt: string | null;
};

type PartnerOffer = {
    id: string;
    code: string;
    name: string;
    rewardType: string;
    pointCost: number | null;
    stockQuantity: number | null;
    userRewardsCount: number;
    description: string | null;
    terms: string | null;
    rewardTier: string | null;
    rewardOption: string | null;
    availableFrom: string | null;
    availableUntil: string | null;
    venueName: string | null;
    campaignName: string | null;
    partnerName: string | null;
    partnerType: string | null;
};

type Props = {
    governance: {
        title: string;
        policy: string;
    };
    stats: {
        ads: number;
        offers: number;
        total: number;
    };
    ads: SmartAd[];
    offers: PartnerOffer[];
};

const placementLabels: Record<string, string> = {
    qr_landing: 'صفحه QR',
    reward_page: 'لحظه پاداش',
    map_route: 'مسیر و نقشه',
    post_mission: 'بعد از ماموریت',
};

function formatDate(value: string | null) {
    if (!value) {
        return 'بدون محدودیت';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
    }).format(new Date(value));
}

function Stat({
    label,
    value,
}: {
    label: string;
    value: number;
}) {
    return (
        <div className="rounded-md border border-white/15 bg-white/10 px-4 py-3">
            <p className="text-xs text-emerald-100">{label}</p>
            <p className="mt-1 text-xl font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

export default function OffersIndex({
    governance,
    stats,
    ads,
    offers,
}: Props) {
    return (
        <>
            <Head title="پیشنهادهای امروز اکسپلوریا" />
            <main dir="rtl" className="min-h-screen bg-stone-50 text-zinc-950">
                <section className="bg-[#063f31] text-white">
                    <div className="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[1fr_auto] lg:px-8">
                        <div>
                            <div className="flex items-center gap-2 text-sm text-emerald-100">
                                <Sparkles className="size-4" />
                                <span>رسانه و پیشنهادهای هوشمند اکسپلوریا</span>
                            </div>
                            <h1 className="mt-3 text-3xl leading-tight font-semibold md:text-4xl">
                                {governance.title}
                            </h1>
                            <p className="mt-4 max-w-3xl text-sm leading-7 text-emerald-50">
                                {governance.policy}
                            </p>
                        </div>
                        <div className="grid grid-cols-3 gap-2 text-center text-sm lg:min-w-80">
                            <Stat label="کل موارد" value={stats.total} />
                            <Stat label="پیشنهادها" value={stats.offers} />
                            <Stat label="آگهی‌ها" value={stats.ads} />
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
                    <div className="grid gap-3 md:grid-cols-3">
                        <div className="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <ShieldCheck className="size-5 text-emerald-700" />
                            <h2 className="mt-3 font-semibold">
                                فقط موارد تاییدشده
                            </h2>
                            <p className="mt-2 text-sm leading-7 text-zinc-600">
                                کاربر نهایی فقط پیشنهادها و آگهی‌هایی را می‌بیند
                                که از مسیر کنترل اکسپلوریا عبور کرده‌اند.
                            </p>
                        </div>
                        <div className="rounded-lg border border-cyan-200 bg-cyan-50 p-4">
                            <Store className="size-5 text-cyan-700" />
                            <h2 className="mt-3 font-semibold">
                                فروشگاه و اسپانسر
                            </h2>
                            <p className="mt-2 text-sm leading-7 text-zinc-600">
                                پیشنهادها می‌توانند از فروشگاه‌ها، واحدهای عضو
                                یا اسپانسرهای تاییدشده وارد چرخه شوند.
                            </p>
                        </div>
                        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <TicketCheck className="size-5 text-amber-700" />
                            <h2 className="mt-3 font-semibold">
                                آماده اتصال به بازی
                            </h2>
                            <p className="mt-2 text-sm leading-7 text-zinc-600">
                                این فهرست پایه اتصال پیشنهادها به مرحله بازی،
                                QR، مسیر نقشه و لحظه پاداش است.
                            </p>
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-4 pb-9 sm:px-6 lg:px-8">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                کوپن، جایزه، حراج و پیشنهاد فروشگاهی
                            </p>
                            <h2 className="text-2xl font-semibold">
                                پیشنهادهای فعال
                            </h2>
                        </div>
                        <Gift className="size-6 text-emerald-700" />
                    </div>

                    {offers.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm leading-7 text-zinc-600">
                            هنوز پیشنهاد تاییدشده‌ای برای نمایش عمومی فعال
                            نیست.
                        </div>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            {offers.map((offer) => (
                                <article
                                    key={offer.id}
                                    className="flex min-h-72 flex-col rounded-lg border border-zinc-200 bg-white p-4 shadow-sm"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="text-xs text-emerald-700">
                                                {offer.partnerName ?? 'اکسپلوریا'}
                                            </p>
                                            <h3 className="mt-1 text-lg leading-7 font-semibold">
                                                {offer.name}
                                            </h3>
                                        </div>
                                        <BadgePercent className="size-5 shrink-0 text-emerald-700" />
                                    </div>
                                    <p className="mt-3 line-clamp-3 text-sm leading-7 text-zinc-600">
                                        {offer.description ??
                                            'پیشنهاد تاییدشده برای استفاده در مسیر تجربه اکسپلوریا.'}
                                    </p>
                                    <div className="mt-4 grid grid-cols-2 gap-2 text-xs">
                                        <div className="rounded-md bg-stone-100 px-3 py-2">
                                            امتیاز:{' '}
                                            {offer.pointCost?.toLocaleString(
                                                'fa-IR',
                                            ) ?? 'بدون امتیاز'}
                                        </div>
                                        <div className="rounded-md bg-stone-100 px-3 py-2">
                                            ظرفیت:{' '}
                                            {offer.stockQuantity?.toLocaleString(
                                                'fa-IR',
                                            ) ?? 'نامحدود'}
                                        </div>
                                    </div>
                                    <p className="mt-3 text-xs leading-6 text-zinc-500">
                                        کمپین: {offer.campaignName ?? '-'} ·
                                        مکان: {offer.venueName ?? '-'}
                                    </p>
                                    <p className="mt-1 text-xs leading-6 text-zinc-500">
                                        اعتبار: {formatDate(offer.availableFrom)}{' '}
                                        تا {formatDate(offer.availableUntil)}
                                    </p>
                                    {offer.terms ? (
                                        <p className="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-900">
                                            {offer.terms}
                                        </p>
                                    ) : null}
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="mx-auto max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                تبلیغ آنلاین، QR، مسیر نقشه و لحظه پاداش
                            </p>
                            <h2 className="text-2xl font-semibold">
                                آگهی‌های تاییدشده
                            </h2>
                        </div>
                        <Megaphone className="size-6 text-cyan-700" />
                    </div>

                    {ads.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-zinc-300 bg-white p-6 text-sm leading-7 text-zinc-600">
                            هنوز آگهی آنلاین تاییدشده‌ای برای این جایگاه‌ها
                            فعال نیست.
                        </div>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2">
                            {ads.map((ad) => (
                                <article
                                    key={ad.id}
                                    className="grid gap-4 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm sm:grid-cols-[9rem_1fr]"
                                >
                                    <div className="flex aspect-square items-center justify-center overflow-hidden rounded-md bg-cyan-50">
                                        {ad.assetUrl &&
                                        ad.creativeType === 'image' ? (
                                            <img
                                                src={ad.assetUrl}
                                                alt=""
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <Megaphone className="size-8 text-cyan-700" />
                                        )}
                                    </div>
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap gap-2 text-xs">
                                            <span className="rounded-full bg-cyan-50 px-2.5 py-1 text-cyan-800">
                                                {placementLabels[
                                                    ad.placementType ?? ''
                                                ] ??
                                                    ad.placementType ??
                                                    'آنلاین'}
                                            </span>
                                            <span className="rounded-full bg-stone-100 px-2.5 py-1 text-zinc-700">
                                                {ad.partnerName ?? 'اکسپلوریا'}
                                            </span>
                                        </div>
                                        <h3 className="mt-3 text-lg leading-7 font-semibold">
                                            {ad.title}
                                        </h3>
                                        <p className="mt-2 line-clamp-3 text-sm leading-7 text-zinc-600">
                                            {ad.bodyCopy ??
                                                'آگهی تاییدشده برای نمایش در مسیرهای آنلاین اکسپلوریا.'}
                                        </p>
                                        <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                                            <p className="flex items-center gap-1 text-xs text-zinc-500">
                                                <CalendarClock className="size-4" />
                                                {formatDate(ad.startsAt)} تا{' '}
                                                {formatDate(ad.endsAt)}
                                            </p>
                                            {ad.targetUrl ? (
                                                <a
                                                    href={ad.targetUrl}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex h-9 items-center gap-2 rounded-md bg-zinc-950 px-3 text-sm font-medium text-white"
                                                >
                                                    {ad.ctaText}
                                                    <ArrowLeft className="size-4" />
                                                </a>
                                            ) : null}
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                <section className="border-t bg-white">
                    <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-5 text-sm sm:px-6 lg:px-8">
                        <p className="text-zinc-600">
                            این صفحه پایه محصول رسانه‌ای اکسپلوریا است و در
                            مرحله بعد به بازی آنلاین و صفحه کاربر متصل می‌شود.
                        </p>
                        <Link
                            href="/"
                            className="inline-flex h-10 items-center gap-2 rounded-md border border-zinc-300 px-3 font-medium"
                        >
                            بازگشت
                            <ArrowLeft className="size-4" />
                        </Link>
                    </div>
                </section>
            </main>
        </>
    );
}
