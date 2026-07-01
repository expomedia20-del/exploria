import { Form, Head } from '@inertiajs/react';
import {
    Building2,
    CircleDot,
    Layers3,
    MapPinned,
    RadioTower,
    Store,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

type HubItem = {
    id: string;
    code: string;
    name: string;
    hubType: string;
    status: string;
    touchpointsCount: number;
    partnersCount: number;
    managerNames: string[];
};

type ZoneItem = {
    id: string;
    code: string;
    name: string;
    status: string;
    hubs: HubItem[];
};

type VenueRegistryItem = {
    id: string;
    code: string;
    name: string;
    city: string | null;
    status: string;
    profileStatus: string;
    zonesCount: number;
    hubsCount: number;
    touchpointsCount: number;
    campaignsCount: number;
    qrCodesCount: number;
    partnerAccountsCount: number;
    locationProfile: {
        venueType: string | null;
        primaryAudience: string | null;
        officialWebsiteUrl: string | null;
        manualResearchNotes: string | null;
        facilities: LocationFacility[];
        constraints: string[];
        updatedAt: string | null;
        readinessScore: number;
    };
    zones: ZoneItem[];
};

type LocationFacility = {
    name: string;
    function: string | null;
    campaignUses: string[];
    priority: 'primary' | 'secondary' | 'low';
    notes: string | null;
};

type Props = {
    venues: VenueRegistryItem[];
};

const statusLabels: Record<string, string> = {
    active: 'فعال',
    draft: 'پیش نویس',
    inactive: 'غیرفعال',
    placeholder: 'نمونه کنترل شده',
};

const statusClasses: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    draft: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
    inactive: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
    placeholder: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
};

const venueTypeLabels: Record<string, string> = {
    ecopark: 'اکوپارک / فضای طبیعت محور',
    amusement_park: 'شهربازی',
    urban_landmark: 'جاذبه شهری / برج',
    mall: 'مرکز خرید',
    event: 'رویداد موقت',
    mixed: 'چندکارکردی',
};

const facilityFunctionLabels: Record<string, string> = {
    education: 'آموزشی',
    entertainment: 'تفریحی',
    retail: 'فروشگاهی',
    rest: 'استراحت',
    route: 'مسیر و جهت‌یابی',
    media: 'تبلیغات و رسانه',
    reward: 'تحویل پاداش',
    discovery: 'کشف و گنج',
};

const campaignUseLabels: Record<string, string> = {
    qr: 'QR',
    mission: 'مأموریت',
    treasure: 'گنج',
    reward: 'پاداش',
    sponsor: 'اسپانسر',
    ad: 'تبلیغ',
    display: 'نمایشگر',
};

const priorityLabels: Record<LocationFacility['priority'], string> = {
    primary: 'اصلی',
    secondary: 'فرعی',
    low: 'کم‌اهمیت',
};

function statusLabel(value: string) {
    return statusLabels[value] ?? value;
}

function lines(items: string[]) {
    return items.join('\n');
}

function facilityNames(items: LocationFacility[]) {
    return items.map((item) => item.name).join('\n');
}

function facilityRows(items: LocationFacility[]) {
    const rows = [...items];

    while (rows.length < 5) {
        rows.push({
            name: '',
            function: null,
            campaignUses: [],
            priority: 'secondary',
            notes: null,
        });
    }

    return rows.slice(0, 12);
}

function Stat({
    icon: Icon,
    label,
    value,
}: {
    icon: LucideIcon;
    label: string;
    value: number;
}) {
    return (
        <div className="rounded-lg border border-sidebar-border/70 px-3 py-2 dark:border-sidebar-border">
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <p>{label}</p>
            </div>
            <p className="mt-1 font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

export default function VenueRegistryIndex({ venues }: Props) {
    const totals = venues.reduce(
        (current, venue) => ({
            zones: current.zones + venue.zonesCount,
            hubs: current.hubs + venue.hubsCount,
            touchpoints: current.touchpoints + venue.touchpointsCount,
            partners: current.partners + venue.partnerAccountsCount,
        }),
        { zones: 0, hubs: 0, touchpoints: 0, partners: 0 },
    );

    return (
        <>
            <Head title="مدیریت مکان‌ها" />
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
                            مدیریت مکان‌ها، هاب‌ها و نقاط تماس
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                        <Stat
                            icon={MapPinned}
                            label="مکان"
                            value={venues.length}
                        />
                        <Stat icon={Layers3} label="هاب" value={totals.hubs} />
                        <Stat
                            icon={RadioTower}
                            label="نقطه تماس"
                            value={totals.touchpoints}
                        />
                        <Stat
                            icon={Store}
                            label="شریک"
                            value={totals.partners}
                        />
                    </div>
                </header>

                <section className="grid gap-4">
                    {venues.length === 0 ? (
                        <div className="rounded-lg border border-sidebar-border/70 p-8 text-center text-sm text-muted-foreground dark:border-sidebar-border">
                            هنوز مکان ثبت نشده است.
                        </div>
                    ) : (
                        venues.map((venue) => (
                            <article
                                key={venue.id}
                                className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border"
                            >
                                <div className="grid gap-4 border-b border-sidebar-border/70 p-4 md:grid-cols-[1.1fr_1.4fr] dark:border-sidebar-border">
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <MapPinned className="size-5 text-muted-foreground" />
                                            <h2 className="text-lg font-semibold">
                                                {venue.name}
                                            </h2>
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    statusClasses[
                                                        venue.status
                                                    ] ?? statusClasses.inactive
                                                }`}
                                            >
                                                {statusLabel(venue.status)}
                                            </span>
                                        </div>
                                        <p
                                            className="mt-2 text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {venue.code}
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {venue.city ?? 'بدون شهر'} · پروفایل{' '}
                                            {statusLabel(venue.profileStatus)}
                                        </p>
                                    </div>

                                    <div className="grid grid-cols-3 gap-2 text-sm md:grid-cols-6">
                                        {(
                                            [
                                                ['زون', venue.zonesCount],
                                                ['هاب', venue.hubsCount],
                                                [
                                                    'نقطه تماس',
                                                    venue.touchpointsCount,
                                                ],
                                                ['کمپین', venue.campaignsCount],
                                                ['QR', venue.qrCodesCount],
                                                [
                                                    'شریک',
                                                    venue.partnerAccountsCount,
                                                ],
                                            ] satisfies [string, number][]
                                        ).map(([label, value]) => (
                                            <div
                                                key={label}
                                                className="rounded-md bg-muted/50 px-3 py-2"
                                            >
                                                <p className="text-muted-foreground">
                                                    {label}
                                                </p>
                                                <p className="mt-1 font-semibold">
                                                    {value.toLocaleString(
                                                        'fa-IR',
                                                    )}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="grid gap-4 border-b border-sidebar-border/70 p-4 lg:grid-cols-[0.9fr_1.1fr] dark:border-sidebar-border">
                                    <div className="rounded-lg bg-muted/35 p-3 text-sm">
                                        <div className="flex items-center justify-between gap-3">
                                            <div>
                                                <h3 className="font-semibold">شناخت‌نامه مکان</h3>
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    مبنای طراحی الگوی کمپین، مأموریت، گنج، پاداش و مسیر برای این مکان.
                                                </p>
                                            </div>
                                            <span className="shrink-0 rounded-full bg-background px-2.5 py-1 text-xs">
                                                آمادگی {venue.locationProfile.readinessScore.toLocaleString('fa-IR')}٪
                                            </span>
                                        </div>
                                        <div className="mt-3 grid gap-2 text-xs text-muted-foreground sm:grid-cols-2">
                                            <p>
                                                نوع مکان:{' '}
                                                <span className="font-medium text-foreground">
                                                    {venue.locationProfile.venueType
                                                        ? venueTypeLabels[venue.locationProfile.venueType] ?? venue.locationProfile.venueType
                                                        : 'ثبت نشده'}
                                                </span>
                                            </p>
                                            <p>
                                                مخاطب غالب:{' '}
                                                <span className="font-medium text-foreground">
                                                    {venue.locationProfile.primaryAudience ?? 'ثبت نشده'}
                                                </span>
                                            </p>
                                            <p>
                                                امکانات/جاذبه‌ها:{' '}
                                                <span className="font-medium text-foreground">
                                                    {venue.locationProfile.facilities.length.toLocaleString('fa-IR')}
                                                </span>
                                            </p>
                                            <p>
                                                محدودیت‌ها:{' '}
                                                <span className="font-medium text-foreground">
                                                    {venue.locationProfile.constraints.length.toLocaleString('fa-IR')}
                                                </span>
                                            </p>
                                        </div>
                                        {venue.locationProfile.facilities.length > 0 ? (
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                {venue.locationProfile.facilities.slice(0, 8).map((facility) => (
                                                    <span key={facility.name} className="rounded-full bg-background px-2.5 py-1 text-xs">
                                                        {facility.name}
                                                    </span>
                                                ))}
                                            </div>
                                        ) : (
                                            <p className="mt-3 rounded-md bg-background px-3 py-2 text-xs text-muted-foreground">
                                                هنوز امکانات و جاذبه‌های این مکان برای طراحی کمپین ثبت نشده است.
                                            </p>
                                        )}
                                    </div>

                                    <Form
                                        action={`/admin/venues/${venue.id}/profile`}
                                        method="patch"
                                        options={{ preserveScroll: true }}
                                        className="grid gap-3 rounded-lg border border-sidebar-border/70 p-3 text-sm dark:border-sidebar-border"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <span className="text-xs font-medium">امکانات و جاذبه‌ها با کارکرد کمپینی</span>
                                                    <div className="grid gap-2">
                                                        {facilityRows(venue.locationProfile.facilities).map((facility, index) => (
                                                            <div key={`${venue.id}-facility-${index}`} className="grid gap-2 rounded-md bg-muted/30 p-2 md:grid-cols-[1fr_0.85fr_1.15fr_0.7fr_1fr]">
                                                                <input
                                                                    name={`facilities[${index}][name]`}
                                                                    defaultValue={facility.name}
                                                                    placeholder="نام امکان/جاذبه"
                                                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                                />
                                                                <select
                                                                    name={`facilities[${index}][function]`}
                                                                    defaultValue={facility.function ?? ''}
                                                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                                >
                                                                    <option value="">کارکرد</option>
                                                                    {Object.entries(facilityFunctionLabels).map(([value, label]) => (
                                                                        <option key={value} value={value}>{label}</option>
                                                                    ))}
                                                                </select>
                                                                <div className="grid grid-cols-2 gap-1 rounded-md border border-input bg-background px-2 py-1 text-xs">
                                                                    {Object.entries(campaignUseLabels).map(([value, label]) => (
                                                                        <label key={value} className="inline-flex items-center gap-1">
                                                                            <input
                                                                                type="checkbox"
                                                                                name={`facilities[${index}][campaign_uses][]`}
                                                                                value={value}
                                                                                defaultChecked={facility.campaignUses.includes(value)}
                                                                            />
                                                                            <span>{label}</span>
                                                                        </label>
                                                                    ))}
                                                                </div>
                                                                <select
                                                                    name={`facilities[${index}][priority]`}
                                                                    defaultValue={facility.priority}
                                                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                                >
                                                                    {Object.entries(priorityLabels).map(([value, label]) => (
                                                                        <option key={value} value={value}>{label}</option>
                                                                    ))}
                                                                </select>
                                                                <input
                                                                    name={`facilities[${index}][notes]`}
                                                                    defaultValue={facility.notes ?? ''}
                                                                    placeholder="یادداشت کوتاه"
                                                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                                />
                                                            </div>
                                                        ))}
                                                    </div>
                                                    {errors.facilities ? <span className="text-xs text-destructive">{errors.facilities}</span> : null}
                                                </div>
                                                <div className="grid gap-3 md:grid-cols-2">
                                                    <label className="grid gap-1">
                                                        <span className="text-xs font-medium">نوع مکان</span>
                                                        <select
                                                            name="venue_type"
                                                            defaultValue={venue.locationProfile.venueType ?? 'mixed'}
                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                        >
                                                            {Object.entries(venueTypeLabels).map(([value, label]) => (
                                                                <option key={value} value={value}>
                                                                    {label}
                                                                </option>
                                                            ))}
                                                        </select>
                                                        {errors.venue_type ? <span className="text-xs text-destructive">{errors.venue_type}</span> : null}
                                                    </label>
                                                    <label className="grid gap-1">
                                                        <span className="text-xs font-medium">مخاطب غالب</span>
                                                        <input
                                                            name="primary_audience"
                                                            defaultValue={venue.locationProfile.primaryAudience ?? ''}
                                                            placeholder="مثلا خانواده، کودک، گردشگر، نوجوان"
                                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                        />
                                                        {errors.primary_audience ? <span className="text-xs text-destructive">{errors.primary_audience}</span> : null}
                                                    </label>
                                                </div>
                                                <label className="grid gap-1">
                                                    <span className="text-xs font-medium">لینک سایت رسمی یا منبع بررسی</span>
                                                    <input
                                                        name="official_website_url"
                                                        defaultValue={venue.locationProfile.officialWebsiteUrl ?? ''}
                                                        dir="ltr"
                                                        placeholder="https://..."
                                                        className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                    />
                                                    {errors.official_website_url ? <span className="text-xs text-destructive">{errors.official_website_url}</span> : null}
                                                </label>
                                                <div className="grid gap-3 md:grid-cols-2">
                                                    <label className="grid gap-1">
                                                        <span className="text-xs font-medium">امکانات و جاذبه‌ها</span>
                                                        <textarea
                                                            name="facilities_text"
                                                            defaultValue={facilityNames(venue.locationProfile.facilities)}
                                                            placeholder="هر مورد در یک خط: دریاچه، مسیر پیاده‌روی، رستوران..."
                                                            className="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                        />
                                                        {errors.facilities_text ? <span className="text-xs text-destructive">{errors.facilities_text}</span> : null}
                                                    </label>
                                                    <label className="grid gap-1">
                                                        <span className="text-xs font-medium">محدودیت‌ها و ملاحظات</span>
                                                        <textarea
                                                            name="constraints_text"
                                                            defaultValue={lines(venue.locationProfile.constraints)}
                                                            placeholder="هر مورد در یک خط: ازدحام، ساعت کاری، نیاز به مجوز..."
                                                            className="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                        />
                                                        {errors.constraints_text ? <span className="text-xs text-destructive">{errors.constraints_text}</span> : null}
                                                    </label>
                                                </div>
                                                <label className="grid gap-1">
                                                    <span className="text-xs font-medium">یادداشت بررسی دستی</span>
                                                    <textarea
                                                        name="manual_research_notes"
                                                        defaultValue={venue.locationProfile.manualResearchNotes ?? ''}
                                                        placeholder="خلاصه شناخت مکان، کارکرد بخش‌ها و فرصت‌های کمپین..."
                                                        className="min-h-20 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                    />
                                                    {errors.manual_research_notes ? <span className="text-xs text-destructive">{errors.manual_research_notes}</span> : null}
                                                </label>
                                                <div className="flex justify-end">
                                                    <button
                                                        type="submit"
                                                        disabled={processing}
                                                        className="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-60"
                                                    >
                                                        ذخیره شناخت‌نامه مکان
                                                    </button>
                                                </div>
                                            </>
                                        )}
                                    </Form>
                                </div>

                                <div className="grid min-w-[940px] grid-cols-[0.9fr_1fr_1fr_0.85fr_0.85fr_1.1fr] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                                    <span>زون</span>
                                    <span>هاب</span>
                                    <span>نوع هاب</span>
                                    <span>نقاط تماس</span>
                                    <span>شرکا</span>
                                    <span>مدیران</span>
                                </div>

                                <div className="min-w-[940px] divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                    {venue.zones.flatMap((zone) =>
                                        zone.hubs.length > 0
                                            ? zone.hubs.map((hub) => (
                                                  <div
                                                      key={`${zone.id}-${hub.id}`}
                                                      className="grid grid-cols-[0.9fr_1fr_1fr_0.85fr_0.85fr_1.1fr] items-center gap-3 px-4 py-3 text-sm"
                                                  >
                                                      <div className="min-w-0">
                                                          <div className="flex items-center gap-2">
                                                              <CircleDot className="size-4 shrink-0 text-muted-foreground" />
                                                              <span className="truncate">
                                                                  {zone.name}
                                                              </span>
                                                          </div>
                                                          <p
                                                              className="mt-1 truncate text-xs text-muted-foreground"
                                                              dir="ltr"
                                                          >
                                                              {zone.code}
                                                          </p>
                                                      </div>
                                                      <div className="min-w-0">
                                                          <div className="flex items-center gap-2">
                                                              <Building2 className="size-4 shrink-0 text-muted-foreground" />
                                                              <span className="truncate font-medium">
                                                                  {hub.name}
                                                              </span>
                                                          </div>
                                                          <p
                                                              className="mt-1 truncate text-xs text-muted-foreground"
                                                              dir="ltr"
                                                          >
                                                              {hub.code}
                                                          </p>
                                                      </div>
                                                      <span>{hub.hubType}</span>
                                                      <span>
                                                          {hub.touchpointsCount.toLocaleString(
                                                              'fa-IR',
                                                          )}
                                                      </span>
                                                      <span>
                                                          {hub.partnersCount.toLocaleString(
                                                              'fa-IR',
                                                          )}
                                                      </span>
                                                      <div className="flex min-w-0 items-center gap-2">
                                                          <UsersRound className="size-4 shrink-0 text-muted-foreground" />
                                                          <span className="truncate">
                                                              {hub.managerNames
                                                                  .length > 0
                                                                  ? hub.managerNames.join(
                                                                        '، ',
                                                                    )
                                                                  : 'بدون مدیر'}
                                                          </span>
                                                      </div>
                                                  </div>
                                              ))
                                            : [
                                                  <div
                                                      key={zone.id}
                                                      className="grid grid-cols-[0.9fr_1fr_1fr_0.85fr_0.85fr_1.1fr] items-center gap-3 px-4 py-3 text-sm text-muted-foreground"
                                                  >
                                                      <span>{zone.name}</span>
                                                      <span>بدون هاب</span>
                                                      <span>-</span>
                                                      <span>۰</span>
                                                      <span>۰</span>
                                                      <span>-</span>
                                                  </div>,
                                              ],
                                    )}
                                </div>
                            </article>
                        ))
                    )}
                </section>
            </div>
        </>
    );
}

VenueRegistryIndex.layout = {
    breadcrumbs: [
        {
            title: 'مدیریت مکان‌ها',
            href: '/admin/venues',
        },
    ],
};
