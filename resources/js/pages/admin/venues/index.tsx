import { Head } from '@inertiajs/react';
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
    zones: ZoneItem[];
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

function statusLabel(value: string) {
    return statusLabels[value] ?? value;
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
