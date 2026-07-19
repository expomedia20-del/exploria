import { Form, Head, Link } from '@inertiajs/react';
import { Activity, ShieldAlert } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

type ScanEventItem = {
    id: string;
    eventType: string;
    result: 'accepted' | 'invalid' | 'duplicate' | null;
    riskFlag: boolean;
    riskReason: string | null;
    objectType: string | null;
    objectId: string | null;
    objectCode: string | null;
    objectLabel: string | null;
    actorLabel: string;
    occurredAt: string;
};

type Props = {
    items: ScanEventItem[];
    summary: {
        total: number;
        scans: number;
        auth: number;
        consent: number;
        journey: number;
        audit: number;
    };
    filters: {
        result: string | null;
        eventType: string | null;
        from: string | null;
        to: string | null;
    };
};

const resultLabels = {
    accepted: 'پذیرفته',
    invalid: 'نامعتبر',
    duplicate: 'تکراری',
};
const summaryLabels = {
    total: 'کل رویدادها',
    scans: 'اسکن‌ها',
    auth: 'ورود و OTP',
    consent: 'رضایت‌نامه',
    journey: 'چرخه تجربه و پاداش',
    audit: 'Audit مدیریتی',
};
const objectTypeLabels: Record<string, string> = {
    qr_code: 'QR',
    venue: 'مکان',
    campaign: 'کمپین',
    mission: 'مأموریت',
    reward: 'پاداش',
    user_reward: 'پاداش کاربر',
    reward_redemption: 'مصرف پاداش',
    ad_request: 'درخواست تبلیغ',
    sponsor_proposal: 'پیشنهاد اسپانسر',
    sponsor_proposal_activation: 'فعال‌سازی اسپانسر',
    partner: 'شریک تجاری',
    treasure: 'گنج',
    user: 'کاربر',
    access_scope: 'دامنه دسترسی',
    consent_version: 'نسخه رضایت‌نامه',
};

export default function ScanEventIndex({ items, summary, filters }: Props) {
    return (
        <div
            className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4"
            dir="rtl"
        >
            <Head title="پایش جامع رویدادها" />
            <header>
                <p className="text-sm text-muted-foreground">
                    Event Monitor · فقط‌خواندنی
                </p>
                <h1 className="mt-1 text-2xl font-semibold">
                    پایش جامع رویدادها
                </h1>
                <p className="mt-2 text-sm text-muted-foreground">
                    نمایش ۱۰۰ رویداد آخر بدون موبایل، IP یا شناسه نشست خام.
                </p>
            </header>

            <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                {Object.entries(summary).map(([key, value]) => (
                    <article
                        key={key}
                        className="rounded-lg border bg-background p-4"
                    >
                        <p className="text-sm text-muted-foreground">
                            {summaryLabels[key as keyof typeof summaryLabels]}
                        </p>
                        <p className="mt-2 text-3xl font-semibold">
                            {value.toLocaleString('fa-IR')}
                        </p>
                    </article>
                ))}
            </section>

            <Form
                action="/admin/events/scan-log"
                method="get"
                className="flex flex-wrap items-end gap-3 rounded-lg border bg-background p-4"
            >
                {({ processing }) => (
                    <>
                        <label className="grid gap-2 text-sm">
                            نتیجه اسکن
                            <select
                                name="result"
                                defaultValue={filters.result ?? ''}
                                className="h-10 min-w-48 rounded-md border bg-background px-3"
                            >
                                <option value="">همه نتایج</option>
                                <option value="accepted">پذیرفته</option>
                                <option value="invalid">نامعتبر</option>
                                <option value="duplicate">تکراری</option>
                            </select>
                        </label>
                        <label className="grid gap-2 text-sm">
                            نوع رویداد
                            <select
                                name="event_type"
                                defaultValue={filters.eventType ?? ''}
                                className="h-10 min-w-52 rounded-md border bg-background px-3"
                            >
                                <option value="">همه رویدادها</option>
                                <option value="otp_requested">
                                    درخواست OTP
                                </option>
                                <option value="otp_verified">تأیید OTP</option>
                                <option value="consent_viewed">
                                    نمایش رضایت‌نامه
                                </option>
                                <option value="consent_accepted">
                                    پذیرش رضایت‌نامه
                                </option>
                                <option value="user_registered">
                                    ثبت‌نام کاربر
                                </option>
                                <option value="mission_started">
                                    شروع مأموریت
                                </option>
                                <option value="mission_completed">
                                    تکمیل مأموریت
                                </option>
                                <option value="reward_issued">
                                    صدور پاداش
                                </option>
                                <option value="reward_redeemed">
                                    مصرف پاداش
                                </option>
                                <option value="qr_scanned">اسکن پذیرفته</option>
                                <option value="invalid_scan">
                                    اسکن نامعتبر
                                </option>
                                <option value="duplicate_scan_flagged">
                                    اسکن تکراری
                                </option>
                                <option value="audit.qr_created">
                                    ساخت QR
                                </option>
                                <option value="audit.qr_updated">
                                    ویرایش QR
                                </option>
                                <option value="audit.qr_deleted">حذف QR</option>
                                <option value="audit.venue_updated">
                                    ویرایش مکان
                                </option>
                                <option value="audit.campaign_created">
                                    ساخت کمپین
                                </option>
                                <option value="audit.campaign_updated">
                                    ویرایش کمپین
                                </option>
                                <option value="audit.campaign_deleted">
                                    حذف کمپین
                                </option>
                                <option value="audit.mission_created">
                                    ساخت مأموریت
                                </option>
                                <option value="audit.mission_updated">
                                    ویرایش مأموریت
                                </option>
                                <option value="audit.mission_deleted">
                                    حذف مأموریت
                                </option>
                                <option value="audit.reward_created">
                                    ساخت پاداش
                                </option>
                                <option value="audit.reward_updated">
                                    ویرایش پاداش
                                </option>
                                <option value="audit.reward_deleted">
                                    حذف پاداش
                                </option>
                                <option value="audit.reward_approved">
                                    تأیید پاداش
                                </option>
                                <option value="audit.reward_rejected">
                                    رد پاداش
                                </option>
                                <option value="audit.reward_revision_requested">
                                    درخواست بازنگری پاداش
                                </option>
                                <option value="audit.user_created">
                                    ساخت حساب مدیریتی
                                </option>
                                <option value="audit.user_role_updated">
                                    تغییر نقش کاربر
                                </option>
                                <option value="audit.user_access_deactivated">
                                    قطع دسترسی‌های کاربر
                                </option>
                                <option value="audit.user_deleted">
                                    حذف کاربر
                                </option>
                                <option value="audit.access_scope_created">
                                    ایجاد دامنه دسترسی
                                </option>
                                <option value="audit.access_scope_reactivated">
                                    فعال‌سازی مجدد دامنه
                                </option>
                                <option value="audit.access_scope_deactivated">
                                    غیرفعال‌سازی دامنه
                                </option>
                                <option value="audit.ad_approved">
                                    تأیید تبلیغ
                                </option>
                                <option value="audit.ad_rejected">
                                    رد تبلیغ
                                </option>
                                <option value="audit.sponsor_proposal_status_updated">
                                    تغییر وضعیت پیشنهاد اسپانسر
                                </option>
                                <option value="audit.sponsor_proposal_activated">
                                    فعال‌سازی پیشنهاد اسپانسر
                                </option>
                                <option value="audit.partner_profile_updated">
                                    ویرایش پروفایل شریک
                                </option>
                                <option value="audit.partner_offer_created">
                                    ثبت پیشنهاد شریک
                                </option>
                                <option value="audit.partner_offer_updated">
                                    ویرایش پیشنهاد شریک
                                </option>
                                <option value="audit.treasure_created">
                                    ساخت گنج
                                </option>
                                <option value="audit.treasure_updated">
                                    ویرایش گنج
                                </option>
                                <option value="audit.treasure_deleted">
                                    حذف گنج
                                </option>
                            </select>
                        </label>
                        <label className="grid gap-2 text-sm">
                            از تاریخ
                            <input
                                type="date"
                                name="from"
                                defaultValue={filters.from ?? ''}
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </label>
                        <label className="grid gap-2 text-sm">
                            تا تاریخ
                            <input
                                type="date"
                                name="to"
                                defaultValue={filters.to ?? ''}
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </label>
                        <Button disabled={processing}>
                            {processing && <Spinner />}اعمال فیلتر
                        </Button>
                        {filters.result ||
                        filters.eventType ||
                        filters.from ||
                        filters.to ? (
                            <Button asChild variant="outline">
                                <Link href="/admin/events/scan-log">
                                    پاک کردن
                                </Link>
                            </Button>
                        ) : null}
                    </>
                )}
            </Form>

            <section className="overflow-hidden rounded-lg border bg-background">
                {items.length === 0 ? (
                    <div className="p-10 text-center text-sm text-muted-foreground">
                        رویدادی مطابق این فیلتر وجود ندارد.
                    </div>
                ) : (
                    <div className="divide-y">
                        {items.map((event) => (
                            <article
                                key={event.id}
                                className="grid gap-3 p-4 md:grid-cols-[1fr_1fr_1fr_auto] md:items-center"
                            >
                                <div className="flex items-center gap-3">
                                    {event.riskFlag ? (
                                        <ShieldAlert className="size-5 text-amber-600" />
                                    ) : (
                                        <Activity className="size-5 text-emerald-600" />
                                    )}
                                    <div>
                                        <p className="font-medium">
                                            {event.result
                                                ? resultLabels[event.result]
                                                : 'رویداد سیستمی'}
                                        </p>
                                        <p
                                            className="text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {event.eventType}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <p>{event.objectLabel ?? 'بدون عنوان'}</p>
                                    <p
                                        className="text-xs text-muted-foreground"
                                        dir="ltr"
                                    >
                                        {event.objectType
                                            ? `${objectTypeLabels[event.objectType] ?? event.objectType}: ${event.objectCode ?? event.objectId ?? '-'}`
                                            : '-'}
                                    </p>
                                </div>
                                <p className="text-sm">{event.actorLabel}</p>
                                <time className="text-sm text-muted-foreground">
                                    {new Intl.DateTimeFormat('fa-IR', {
                                        dateStyle: 'medium',
                                        timeStyle: 'short',
                                    }).format(new Date(event.occurredAt))}
                                </time>
                            </article>
                        ))}
                    </div>
                )}
            </section>
        </div>
    );
}

ScanEventIndex.layout = {
    breadcrumbs: [
        { title: 'پایش جامع رویدادها', href: '/admin/events/scan-log' },
    ],
};
