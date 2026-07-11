import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Props = {
    qr: {
        code: string;
        label: string;
        venueName: string;
        city: string;
        zoneName: string;
        hubName: string;
        touchpointLabel: string;
        campaignName: string;
        isDemo: boolean;
    };
    missionPreview: {
        id: string;
        title: string | null;
        description: string | null;
        points: number;
        displayStep: number;
        cycleStep: { index: number | null; label: string | null };
        evidence: string;
        hubName: string | null;
        touchpointLabel: string | null;
        treasureName: string | null;
    }[];
    rewardOptions: {
        id: string;
        name: string;
        tier: string | null;
        option: string | null;
        partnerName: string | null;
        description: string | null;
    }[];
};

const rewardTierLabels: Record<string, string> = {
    bronze: 'برنزی',
    silver: 'نقره‌ای',
    gold: 'طلایی',
    diamond: 'الماسی',
    custom: 'سفارشی',
};

const scanHeroImage = '/images/ecopark/proposal/qr-backpack-route-16-9.jpg';

export default function ScanLanding({
    qr,
    missionPreview,
    rewardOptions,
}: Props) {
    return (
        <main
            dir="rtl"
            className="min-h-screen bg-slate-950 px-4 py-6 text-slate-950 sm:py-10"
        >
            <Head title={`بازدید ${qr.venueName}`} />

            <section className="mx-auto grid w-full max-w-6xl overflow-hidden rounded-lg border border-white/10 bg-white shadow-2xl shadow-black/30 lg:min-h-[720px] lg:grid-cols-[0.9fr_1.1fr]">
                <div className="relative min-h-72 lg:min-h-full">
                    <img
                        src={scanHeroImage}
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent lg:bg-gradient-to-l" />
                    <div className="absolute inset-x-0 bottom-0 p-5 text-white sm:p-7">
                        <span className="rounded-full bg-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-950">
                            {qr.isDemo ? 'دموی اکوپارک' : 'پایلوت اکسپلوریا'}
                        </span>
                        <h1 className="mt-4 text-3xl leading-tight font-bold">
                            {qr.venueName}
                        </h1>
                        <p className="mt-2 text-sm text-slate-200">
                            {qr.city} · {qr.campaignName}
                        </p>
                    </div>
                </div>

                <div className="p-5 sm:p-8">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                            پایلوت اکسپلوریا
                        </span>
                        {qr.isDemo && (
                            <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                داده آزمایشی
                            </span>
                        )}
                    </div>

                    <div className="mt-8">
                        <p className="text-sm text-slate-500">
                            به تجربه مکان خوش آمدید
                        </p>
                        <h2 className="mt-2 text-2xl font-bold">
                            مسیر بازدید شما آماده است
                        </h2>
                        <p className="mt-2 text-sm leading-7 text-slate-600">
                            پس از ورود سریع، ماموریت‌ها، گنج‌ها و گزینه‌های
                            پاداش همین کمپین برای شما فعال می‌شود.
                        </p>
                    </div>

                    <dl className="mt-8 grid gap-4 rounded-lg bg-slate-50 p-5 text-sm">
                        <div className="flex justify-between gap-4">
                            <dt className="text-slate-500">محدوده</dt>
                            <dd className="font-medium">{qr.zoneName}</dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-slate-500">هاب</dt>
                            <dd className="font-medium">{qr.hubName}</dd>
                        </div>
                        <div className="flex justify-between gap-4">
                            <dt className="text-slate-500">نقطه تعامل</dt>
                            <dd className="font-medium">
                                {qr.touchpointLabel}
                            </dd>
                        </div>
                    </dl>

                    <p className="mt-6 text-sm leading-7 text-slate-600">
                        برای ثبت بازدید و ادامه تجربه پایلوت، ورود سریع و پذیرش
                        رضایت‌نامه لازم است.
                    </p>

                    {missionPreview.length > 0 ? (
                        <section className="mt-6 rounded-lg border border-sky-100 bg-sky-50/70 p-4">
                            <div className="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <h2 className="text-sm font-semibold">
                                        مسیر مأموریت‌های این کمپین
                                    </h2>
                                    <p className="mt-1 text-xs leading-6 text-slate-600">
                                        بعد از ورود، همین گام‌ها برای شما باز
                                        می‌شود و با تکمیل آن‌ها امتیاز، گنج یا
                                        پاداش می‌گیرید.
                                    </p>
                                </div>
                                <span className="rounded-full bg-white px-2.5 py-1 text-xs text-sky-800 shadow-xs">
                                    {missionPreview.length.toLocaleString(
                                        'fa-IR',
                                    )}{' '}
                                    گام
                                </span>
                            </div>
                            <div className="mt-3 grid gap-2">
                                {missionPreview.map((mission) => (
                                    <article
                                        key={mission.id}
                                        className="rounded-md bg-white px-3 py-3 text-sm shadow-xs"
                                    >
                                        <div className="flex items-start gap-3">
                                            <span className="flex size-7 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-semibold text-sky-800">
                                                {mission.displayStep.toLocaleString(
                                                    'fa-IR',
                                                )}
                                            </span>
                                            <div className="min-w-0">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h3 className="font-medium">
                                                        {mission.title ??
                                                            'ماموریت کمپین'}
                                                    </h3>
                                                    <span className="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] text-amber-900">
                                                        {mission.points.toLocaleString(
                                                            'fa-IR',
                                                        )}{' '}
                                                        امتیاز
                                                    </span>
                                                </div>
                                                <p className="mt-1 text-xs leading-6 text-slate-600">
                                                    {mission.description}
                                                </p>
                                                <div className="mt-2 flex flex-wrap gap-2 text-[11px] text-slate-600">
                                                    <span className="rounded-full bg-slate-100 px-2 py-0.5">
                                                        {mission.cycleStep
                                                            .label ??
                                                            'مسیر اصلی'}
                                                    </span>
                                                    <span className="rounded-full bg-slate-100 px-2 py-0.5">
                                                        مدرک: {mission.evidence}
                                                    </span>
                                                    {mission.treasureName ? (
                                                        <span className="rounded-full bg-rose-100 px-2 py-0.5 text-rose-900">
                                                            گنج:{' '}
                                                            {
                                                                mission.treasureName
                                                            }
                                                        </span>
                                                    ) : null}
                                                    {(mission.hubName ??
                                                    mission.touchpointLabel) ? (
                                                        <span className="rounded-full bg-slate-100 px-2 py-0.5">
                                                            {mission.hubName ??
                                                                mission.touchpointLabel}
                                                        </span>
                                                    ) : null}
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        </section>
                    ) : null}

                    {rewardOptions.length > 0 ? (
                        <section className="mt-6 rounded-lg border border-emerald-100 bg-emerald-50/70 p-4">
                            <h2 className="text-sm font-semibold">
                                گزینه‌های پاداش این کمپین
                            </h2>
                            <div className="mt-3 grid gap-2">
                                {rewardOptions.map((reward) => (
                                    <article
                                        key={reward.id}
                                        className="rounded-md bg-white px-3 py-2 text-sm shadow-xs"
                                    >
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <p className="font-medium">
                                                {reward.name}
                                            </p>
                                            <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-800">
                                                {reward.tier
                                                    ? (rewardTierLabels[
                                                          reward.tier
                                                      ] ?? reward.tier)
                                                    : 'عمومی'}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-xs text-slate-600">
                                            {reward.option ??
                                                'گزینه جایزه توسط کمپین تعیین می‌شود'}
                                            {reward.partnerName
                                                ? ` · ${reward.partnerName}`
                                                : ''}
                                        </p>
                                        {reward.description ? (
                                            <p className="mt-1 line-clamp-2 text-xs text-slate-500">
                                                {reward.description}
                                            </p>
                                        ) : null}
                                    </article>
                                ))}
                            </div>
                        </section>
                    ) : null}

                    <Button
                        className="mt-6 h-11 w-full"
                        onClick={() =>
                            window.location.assign(
                                `/access?sourceQrCode=${encodeURIComponent(qr.code)}`,
                            )
                        }
                    >
                        شروع تجربه
                    </Button>
                </div>
            </section>
        </main>
    );
}
