import { Head, Link } from '@inertiajs/react';
import {
    Building2,
    MapPinned,
    ShieldCheck,
    Store,
    UserRound,
} from 'lucide-react';

type RegistryEntity = {
    id: string;
    code: string;
    name: string;
};

type PartnerLocation = {
    id: string;
    locationRole: string;
    status: string;
    hub: (RegistryEntity & { hubType: string }) | null;
    zone: RegistryEntity | null;
};

type PartnerUser = {
    id: string;
    role: string;
    status: string;
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
    } | null;
};

type PartnerRegistryItem = {
    id: string;
    code: string;
    name: string;
    partnerType: string;
    status: string;
    contactName: string | null;
    contactMobile: string | null;
    venue: RegistryEntity | null;
    locations: PartnerLocation[];
    users: PartnerUser[];
};

type Props = {
    partners: PartnerRegistryItem[];
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش نویس',
    inactive: 'غیرفعال',
    placeholder: 'نمونه کنترل شده',
};

const partnerTypeLabels: Record<string, string> = {
    member_shop: 'فروشگاه عضو',
    sponsor: 'اسپانسر',
    external_brand: 'برند غیرعضو',
    hub_subunit: 'زیرمجموعه هاب',
};

const statusClasses: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    draft: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
    inactive: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    placeholder: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
};

function statusLabel(value: string) {
    return statusLabels[value] ?? value;
}

function partnerTypeLabel(value: string) {
    return partnerTypeLabels[value] ?? value;
}

function firstLocation(partner: PartnerRegistryItem) {
    return partner.locations[0] ?? null;
}

function firstUser(partner: PartnerRegistryItem) {
    return partner.users[0]?.user ?? null;
}

export default function PartnerRegistryIndex({ partners }: Props) {
    const activeCount = partners.filter(
        (partner) => partner.status === 'active',
    ).length;
    const sponsorCount = partners.filter(
        (partner) => partner.partnerType === 'sponsor',
    ).length;

    return (
        <>
            <Head title="مدیریت شرکا" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            رجیستری فاز ۱ واقعی
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            مدیریت شرکا و زیرمجموعه‌های تجاری
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                            <p className="text-muted-foreground">کل شرکا</p>
                            <p className="mt-1 font-semibold">
                                {partners.length.toLocaleString('fa-IR')}
                            </p>
                        </div>
                        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                            <p className="text-muted-foreground">فعال</p>
                            <p className="mt-1 font-semibold">
                                {activeCount.toLocaleString('fa-IR')}
                            </p>
                        </div>
                        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
                            <p className="text-muted-foreground">اسپانسر</p>
                            <p className="mt-1 font-semibold">
                                {sponsorCount.toLocaleString('fa-IR')}
                            </p>
                        </div>
                    </div>
                </header>

                <section className="grid gap-3 md:grid-cols-3">
                    <Link
                        href="/admin/access-scopes"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">اتصال اکانت به شریک</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            بعد از ثبت فروشگاه یا اسپانسر، دسترسی عملیاتی اکانت
                            مسئول را تعیین کنید.
                        </p>
                    </Link>
                    <Link
                        href="/partner/dashboard"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">پنل فروشگاه/شریک</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            پیشنهاد پاداش، مصرف کد و گزارش عملکرد از پنل شریک
                            پیگیری می‌شود.
                        </p>
                    </Link>
                    <Link
                        href="/admin/ads"
                        className="rounded-lg border border-sidebar-border/70 bg-background p-3 text-sm dark:border-sidebar-border"
                    >
                        <p className="font-semibold">تبلیغات مستقل شریک</p>
                        <p className="mt-2 leading-6 text-muted-foreground">
                            تبلیغات فروشگاه یا اسپانسر بعد از ثبت، از صف تایید
                            تبلیغات عبور می‌کند.
                        </p>
                    </Link>
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="grid min-w-[980px] grid-cols-[1.15fr_0.85fr_1fr_1fr_1fr_0.9fr] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                        <span>شریک</span>
                        <span>نوع</span>
                        <span>مکان و هاب</span>
                        <span>کاربر مسئول</span>
                        <span>تماس</span>
                        <span>وضعیت</span>
                    </div>

                    {partners.length === 0 ? (
                        <div className="p-8 text-center text-sm text-muted-foreground">
                            هنوز شریک تجاری ثبت نشده است.
                        </div>
                    ) : (
                        <div className="min-w-[980px] divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {partners.map((partner) => {
                                const location = firstLocation(partner);
                                const user = firstUser(partner);

                                return (
                                    <article
                                        key={partner.id}
                                        className="grid grid-cols-[1.15fr_0.85fr_1fr_1fr_1fr_0.9fr] items-center gap-3 px-4 py-3 text-sm"
                                    >
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <Store className="size-4 shrink-0 text-muted-foreground" />
                                                <span className="truncate font-medium">
                                                    {partner.name}
                                                </span>
                                            </div>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {partner.code}
                                            </p>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <ShieldCheck className="size-4 shrink-0 text-muted-foreground" />
                                            <span>
                                                {partnerTypeLabel(
                                                    partner.partnerType,
                                                )}
                                            </span>
                                        </div>

                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <MapPinned className="size-4 shrink-0 text-muted-foreground" />
                                                <span className="truncate">
                                                    {location?.hub?.name ??
                                                        partner.venue?.name ??
                                                        'بدون اتصال'}
                                                </span>
                                            </div>
                                            <p className="mt-1 truncate text-xs text-muted-foreground">
                                                {location?.locationRole ?? '-'}
                                            </p>
                                        </div>

                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <UserRound className="size-4 shrink-0 text-muted-foreground" />
                                                <span className="truncate">
                                                    {user?.name ?? 'بدون کاربر'}
                                                </span>
                                            </div>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {user?.email ?? '-'}
                                            </p>
                                        </div>

                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <Building2 className="size-4 shrink-0 text-muted-foreground" />
                                                <span className="truncate">
                                                    {partner.contactName ??
                                                        'بدون مخاطب'}
                                                </span>
                                            </div>
                                            <p
                                                className="mt-1 truncate text-xs text-muted-foreground"
                                                dir="ltr"
                                            >
                                                {partner.contactMobile ?? '-'}
                                            </p>
                                        </div>

                                        <span
                                            className={`inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-medium ${
                                                statusClasses[partner.status] ??
                                                statusClasses.inactive
                                            }`}
                                        >
                                            {statusLabel(partner.status)}
                                        </span>
                                    </article>
                                );
                            })}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

PartnerRegistryIndex.layout = {
    breadcrumbs: [
        {
            title: 'مدیریت شرکا',
            href: '/admin/partners',
        },
    ],
};
