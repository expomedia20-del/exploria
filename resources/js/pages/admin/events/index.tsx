import { Form, Head, Link } from '@inertiajs/react';
import { Activity, ShieldAlert } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

type ScanEventItem = {
    id: string;
    eventType: string;
    result: 'accepted' | 'invalid' | 'duplicate';
    riskFlag: boolean;
    riskReason: string | null;
    qrCode: string | null;
    qrLabel: string | null;
    actorLabel: string;
    scannedAt: string;
};

type Props = {
    items: ScanEventItem[];
    summary: {
        total: number;
        accepted: number;
        invalid: number;
        duplicate: number;
    };
    filters: { result: string | null };
};

const resultLabels = {
    accepted: 'پذیرفته',
    invalid: 'نامعتبر',
    duplicate: 'تکراری',
};

export default function ScanEventIndex({ items, summary, filters }: Props) {
    return (
        <div
            className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4"
            dir="rtl"
        >
            <Head title="پایش رویدادهای اسکن" />
            <header>
                <p className="text-sm text-muted-foreground">
                    Event Monitor · فقط‌خواندنی
                </p>
                <h1 className="mt-1 text-2xl font-semibold">
                    پایش رویدادهای اسکن
                </h1>
                <p className="mt-2 text-sm text-muted-foreground">
                    نمایش ۱۰۰ رویداد آخر بدون موبایل، IP یا شناسه نشست خام.
                </p>
            </header>

            <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {Object.entries(summary).map(([key, value]) => (
                    <article
                        key={key}
                        className="rounded-lg border bg-background p-4"
                    >
                        <p className="text-sm text-muted-foreground">
                            {key === 'total'
                                ? 'کل رویدادها'
                                : resultLabels[
                                      key as keyof typeof resultLabels
                                  ]}
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
                        <Button disabled={processing}>
                            {processing && <Spinner />}اعمال فیلتر
                        </Button>
                        {filters.result ? (
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
                                            {resultLabels[event.result]}
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
                                    <p>{event.qrLabel ?? 'QR بدون عنوان'}</p>
                                    <p
                                        className="text-xs text-muted-foreground"
                                        dir="ltr"
                                    >
                                        {event.qrCode ?? '-'}
                                    </p>
                                </div>
                                <p className="text-sm">{event.actorLabel}</p>
                                <time className="text-sm text-muted-foreground">
                                    {new Intl.DateTimeFormat('fa-IR', {
                                        dateStyle: 'medium',
                                        timeStyle: 'short',
                                    }).format(new Date(event.scannedAt))}
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
        { title: 'پایش رویدادهای اسکن', href: '/admin/events/scan-log' },
    ],
};
