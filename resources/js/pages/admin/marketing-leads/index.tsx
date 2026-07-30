import { Form, Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    CalendarClock,
    CheckCircle2,
    Clock3,
    Inbox,
    MapPin,
    Phone,
    UserRound,
} from 'lucide-react';
import { InternalTeamNavigation } from '@/components/dashboard/internal-team-navigation';

type LeadStatus = 'new' | 'reviewing' | 'demo_scheduled' | 'closed';

type MarketingLead = {
    id: string;
    audienceLabel: string;
    organizationName: string | null;
    contactName: string;
    mobile: string;
    city: string | null;
    projectHint: string | null;
    notes: string | null;
    status: LeadStatus;
    statusLabel: string;
    sourcePath: string | null;
    internalNotes: string | null;
    createdAtLabel: string | null;
};

type Props = {
    leads: MarketingLead[];
    stats: Record<'total' | LeadStatus, number>;
    statusOptions: Record<LeadStatus, string>;
    filters: {
        status: LeadStatus | null;
    };
};

const filterTabs: Array<{
    key: LeadStatus | null;
    label: string;
    countKey: 'total' | LeadStatus;
}> = [
    { key: null, label: 'همه درخواست‌ها', countKey: 'total' },
    { key: 'new', label: 'جدید', countKey: 'new' },
    { key: 'reviewing', label: 'در حال پیگیری', countKey: 'reviewing' },
    {
        key: 'demo_scheduled',
        label: 'دمو زمان‌بندی شد',
        countKey: 'demo_scheduled',
    },
    { key: 'closed', label: 'بسته شده', countKey: 'closed' },
];

const statusTone: Record<LeadStatus, string> = {
    new: 'bg-amber-100 text-amber-950',
    reviewing: 'bg-cyan-100 text-cyan-950',
    demo_scheduled: 'bg-emerald-100 text-emerald-950',
    closed: 'bg-zinc-100 text-zinc-700',
};

export default function MarketingLeadInboxIndex({
    leads,
    stats,
    statusOptions,
    filters,
}: Props) {
    return (
        <>
            <Head title="صندوق درخواست‌های دمو" />

            <main className="space-y-4 p-4" dir="rtl">
                <section className="rounded-lg border border-sidebar-border/70 bg-gradient-to-l from-emerald-50 via-background to-cyan-50 p-4 dark:border-sidebar-border dark:from-emerald-950/25 dark:to-cyan-950/20">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p className="text-sm text-muted-foreground">
                                پیگیری درخواست‌های ورودی از صفحات معرفی و SEO
                            </p>
                            <h1 className="mt-1 text-2xl font-semibold">
                                صندوق درخواست‌های دمو
                            </h1>
                            <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                                هر فرم درخواست دمو که از صفحه اصلی یا صفحات
                                راهکار ثبت شود اینجا وارد صف پیگیری می‌شود. تیم
                                داخلی می‌تواند وضعیت تماس، زمان‌بندی دمو و
                                یادداشت هماهنگی را همین‌جا نگه دارد.
                            </p>
                        </div>
                        <Link
                            href="/"
                            className="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-input bg-background px-3 text-sm font-medium"
                        >
                            صفحات معرفی
                            <ArrowLeft className="size-4" />
                        </Link>
                    </div>
                </section>

                <InternalTeamNavigation activeHref="/admin/marketing-leads" />

                <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    {filterTabs.map((tab) => {
                        const isActive = filters.status === tab.key;
                        const href = tab.key
                            ? `/admin/marketing-leads?status=${tab.key}`
                            : '/admin/marketing-leads';

                        return (
                            <Link
                                key={tab.label}
                                href={href}
                                className={`rounded-lg border p-4 transition ${
                                    isActive
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-sidebar-border/70 bg-card hover:border-primary/40 dark:border-sidebar-border'
                                }`}
                            >
                                <p className="text-sm font-medium">
                                    {tab.label}
                                </p>
                                <p className="mt-2 text-2xl font-semibold">
                                    {(stats[tab.countKey] ?? 0).toLocaleString(
                                        'fa-IR',
                                    )}
                                </p>
                            </Link>
                        );
                    })}
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-card dark:border-sidebar-border">
                    <div className="flex flex-col gap-2 border-b border-sidebar-border/70 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-sidebar-border">
                        <div className="flex items-center gap-2">
                            <Inbox className="size-5 text-primary" />
                            <h2 className="font-semibold">
                                درخواست‌های قابل پیگیری
                            </h2>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            {leads.length.toLocaleString('fa-IR')} مورد در نمای
                            فعلی
                        </p>
                    </div>

                    {leads.length === 0 ? (
                        <div className="p-8 text-center">
                            <Inbox className="mx-auto size-8 text-muted-foreground" />
                            <h3 className="mt-3 font-semibold">
                                درخواستی برای این فیلتر ثبت نشده است.
                            </h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                با ثبت فرم «درخواست دمو» در صفحات معرفی،
                                رکوردهای جدید اینجا دیده می‌شوند.
                            </p>
                        </div>
                    ) : (
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {leads.map((lead) => (
                                <article
                                    key={lead.id}
                                    className="grid gap-4 p-4 xl:grid-cols-[1.2fr_0.8fr]"
                                >
                                    <div className="space-y-3">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span
                                                className={`rounded-full px-3 py-1 text-xs font-medium ${statusTone[lead.status]}`}
                                            >
                                                {lead.statusLabel}
                                            </span>
                                            <span className="rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">
                                                {lead.audienceLabel}
                                            </span>
                                            {lead.createdAtLabel ? (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">
                                                    <CalendarClock className="size-3.5" />
                                                    {lead.createdAtLabel}
                                                </span>
                                            ) : null}
                                        </div>

                                        <div>
                                            <h3 className="text-lg font-semibold">
                                                {lead.organizationName ??
                                                    'سازمان ثبت نشده'}
                                            </h3>
                                            <div className="mt-2 grid gap-2 text-sm text-muted-foreground sm:grid-cols-2 lg:grid-cols-4">
                                                <span className="inline-flex items-center gap-2">
                                                    <UserRound className="size-4" />
                                                    {lead.contactName}
                                                </span>
                                                <span
                                                    className="inline-flex items-center gap-2"
                                                    dir="ltr"
                                                >
                                                    <Phone className="size-4" />
                                                    {lead.mobile}
                                                </span>
                                                <span className="inline-flex items-center gap-2">
                                                    <MapPin className="size-4" />
                                                    {lead.city ?? 'شهر نامشخص'}
                                                </span>
                                                <span className="inline-flex items-center gap-2">
                                                    <Building2 className="size-4" />
                                                    {lead.sourcePath ?? '/'}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="grid gap-3 text-sm leading-7 md:grid-cols-2">
                                            <div className="rounded-md bg-muted/35 p-3">
                                                <p className="font-medium text-foreground">
                                                    اشاره پروژه
                                                </p>
                                                <p className="mt-1 text-muted-foreground">
                                                    {lead.projectHint ??
                                                        'ثبت نشده'}
                                                </p>
                                            </div>
                                            <div className="rounded-md bg-muted/35 p-3">
                                                <p className="font-medium text-foreground">
                                                    توضیحات درخواست
                                                </p>
                                                <p className="mt-1 text-muted-foreground">
                                                    {lead.notes ?? 'ثبت نشده'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <Form
                                        action={`/admin/marketing-leads/${lead.id}/status`}
                                        method="patch"
                                        options={{ preserveScroll: true }}
                                        className="grid gap-3 rounded-lg border border-sidebar-border/70 bg-background p-3 dark:border-sidebar-border"
                                    >
                                        <div className="flex items-center gap-2 text-sm font-semibold">
                                            <Clock3 className="size-4 text-primary" />
                                            پیگیری داخلی
                                        </div>
                                        <label className="grid gap-1 text-sm">
                                            <span className="font-medium">
                                                وضعیت
                                            </span>
                                            <select
                                                name="status"
                                                defaultValue={lead.status}
                                                className="h-10 rounded-md border border-input bg-background px-3"
                                            >
                                                {Object.entries(
                                                    statusOptions,
                                                ).map(([value, label]) => (
                                                    <option
                                                        key={value}
                                                        value={value}
                                                    >
                                                        {label}
                                                    </option>
                                                ))}
                                            </select>
                                        </label>
                                        <label className="grid gap-1 text-sm">
                                            <span className="font-medium">
                                                یادداشت داخلی
                                            </span>
                                            <textarea
                                                name="internal_notes"
                                                defaultValue={
                                                    lead.internalNotes ?? ''
                                                }
                                                rows={4}
                                                className="resize-y rounded-md border border-input bg-background px-3 py-2 leading-7"
                                                placeholder="مثلا: تماس اول انجام شد، زمان پیشنهادی دمو..."
                                            />
                                        </label>
                                        <button
                                            type="submit"
                                            className="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground"
                                        >
                                            <CheckCircle2 className="size-4" />
                                            ذخیره پیگیری
                                        </button>
                                    </Form>
                                </article>
                            ))}
                        </div>
                    )}
                </section>
            </main>
        </>
    );
}
