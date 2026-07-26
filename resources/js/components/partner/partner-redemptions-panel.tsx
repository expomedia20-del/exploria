import { Form } from '@inertiajs/react';
import {
    CheckCircle2,
    CircleAlert,
    Clock3,
    History,
    ReceiptText,
    ScanLine,
    ShoppingCart,
} from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    formatPartnerDate,
    partnerStatusLabels,
} from '@/components/partner/partner-dashboard-utils';
import type {
    PartnerDashboardStats,
    Redemption,
} from '@/types/partner-dashboard';

function PendingRedemptionRow({ redemption }: { redemption: Redemption }) {
    return (
        <article className="grid gap-3 p-4 md:grid-cols-[1fr_auto] md:items-center">
            <div>
                <p className="font-medium">
                    {redemption.rewardName ?? 'پاداش بدون عنوان'}
                </p>
                <p className="mt-1 text-xs text-muted-foreground">
                    مشتری: {redemption.visitorName ?? 'ثبت نشده'} · کمپین:{' '}
                    {redemption.campaignName ??
                        redemption.campaignCode ??
                        'ثبت نشده'}
                </p>
                <p
                    className="mt-2 w-fit rounded bg-background px-2 py-1 font-mono text-sm"
                    dir="ltr"
                >
                    {redemption.redemptionCode}
                </p>
            </div>
            <Form
                action="/partner/redemptions/confirm"
                method="post"
                options={{ preserveScroll: true }}
            >
                {({ processing }) => (
                    <>
                        <input
                            type="hidden"
                            name="redemption_code"
                            value={redemption.redemptionCode}
                        />
                        <input
                            type="hidden"
                            name="purchase_status"
                            value="reward_only"
                        />
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={processing}
                        >
                            <CheckCircle2 className="size-4" />
                            تحویل بدون ثبت خرید
                        </Button>
                    </>
                )}
            </Form>
        </article>
    );
}

export function PartnerRedemptionsPanel({
    redemptions,
    stats,
}: {
    redemptions: Redemption[];
    stats: PartnerDashboardStats;
}) {
    const [purchaseStatus, setPurchaseStatus] = useState('reward_only');
    const pendingRedemptions = redemptions.filter(
        (redemption) => redemption.status === 'pending',
    );
    const completedRedemptions = redemptions.filter(
        (redemption) => redemption.status !== 'pending',
    );

    return (
        <div className="grid gap-4">
            <section className="rounded-xl border border-primary/25 bg-background">
                <div className="flex flex-col gap-3 border-b border-sidebar-border/70 p-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <ScanLine className="size-5 text-primary" />
                            <h2 className="text-lg font-semibold">
                                تحویل پاداش به مشتری
                            </h2>
                        </div>
                        <p className="mt-2 max-w-2xl text-sm leading-7 text-muted-foreground">
                            کدی را که مشتری در پنل خود می‌بیند وارد کنید. اگر
                            خرید واقعی انجام شده، نتیجه و مبلغ رسید را هم ثبت
                            کنید؛ خرید شرط تحویل پاداش نیست.
                        </p>
                    </div>
                    <span
                        className={`flex w-fit items-center gap-1.5 rounded-full px-3 py-1 text-xs ${
                            stats.pendingRedemptions > 0
                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                        }`}
                    >
                        {stats.pendingRedemptions > 0 ? (
                            <CircleAlert className="size-3.5" />
                        ) : (
                            <CheckCircle2 className="size-3.5" />
                        )}
                        {stats.pendingRedemptions.toLocaleString('fa-IR')} مورد
                        در انتظار
                    </span>
                </div>

                <Form
                    action="/partner/redemptions/confirm"
                    method="post"
                    options={{ preserveScroll: true }}
                    className="grid gap-4 p-4 md:grid-cols-2"
                >
                    {({ processing, errors }) => (
                        <>
                            {Object.keys(errors).length > 0 ? (
                                <p
                                    role="alert"
                                    className="rounded-lg border border-destructive/25 bg-destructive/5 p-3 text-sm text-destructive md:col-span-2"
                                >
                                    تحویل ثبت نشد. کد و اطلاعات خرید را بررسی
                                    کنید.
                                </p>
                            ) : null}
                            <div className="grid gap-2">
                                <Label htmlFor="redemption_code">
                                    کد مصرف مشتری
                                </Label>
                                <Input
                                    id="redemption_code"
                                    name="redemption_code"
                                    required
                                    dir="ltr"
                                    autoComplete="off"
                                    placeholder="مثلاً X4K8P2M9Q1"
                                />
                                <p className="text-xs text-muted-foreground">
                                    کد را از صفحه کیف پاداش مشتری بخوانید یا کپی
                                    کنید.
                                </p>
                                <InputError message={errors.redemption_code} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="purchase_status">
                                    نتیجه مراجعه
                                </Label>
                                <select
                                    id="purchase_status"
                                    name="purchase_status"
                                    value={purchaseStatus}
                                    onChange={(event) =>
                                        setPurchaseStatus(event.target.value)
                                    }
                                    className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    <option value="reward_only">
                                        فقط تحویل پاداش
                                    </option>
                                    <option value="purchase_confirmed">
                                        تحویل پاداش همراه خرید واقعی
                                    </option>
                                </select>
                                <InputError message={errors.purchase_status} />
                            </div>
                            {purchaseStatus === 'purchase_confirmed' ? (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="purchase_amount">
                                            مبلغ خرید (ریال)
                                        </Label>
                                        <Input
                                            id="purchase_amount"
                                            name="purchase_amount"
                                            type="number"
                                            min="1"
                                            required
                                            inputMode="numeric"
                                            placeholder="مثلاً ۵۰۰۰۰۰۰"
                                        />
                                        <InputError
                                            message={errors.purchase_amount}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="receipt_reference">
                                            شماره رسید یا فاکتور (اختیاری)
                                        </Label>
                                        <Input
                                            id="receipt_reference"
                                            name="receipt_reference"
                                            maxLength={100}
                                            placeholder="مرجع قابل پیگیری"
                                        />
                                        <InputError
                                            message={errors.receipt_reference}
                                        />
                                    </div>
                                </>
                            ) : null}
                            <div className="flex justify-end md:col-span-2">
                                <Button disabled={processing}>
                                    <CheckCircle2 className="size-4" />
                                    {processing
                                        ? 'در حال ثبت...'
                                        : 'ثبت تحویل و نتیجه مراجعه'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </section>

            {pendingRedemptions.length > 0 ? (
                <section className="rounded-xl border border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20">
                    <div className="flex items-center gap-2 border-b border-amber-200 p-4 dark:border-amber-900">
                        <Clock3 className="size-4 text-amber-700 dark:text-amber-200" />
                        <h2 className="font-semibold">
                            کدهای آماده تحویل در فروشگاه
                        </h2>
                    </div>
                    <div className="divide-y divide-amber-200 dark:divide-amber-900">
                        {pendingRedemptions.slice(0, 3).map((redemption) => (
                            <PendingRedemptionRow
                                key={redemption.id}
                                redemption={redemption}
                            />
                        ))}
                        {pendingRedemptions.length > 3 ? (
                            <details className="group">
                                <summary className="cursor-pointer list-none p-4 text-sm font-medium text-amber-900 dark:text-amber-100">
                                    نمایش{' '}
                                    {(
                                        pendingRedemptions.length - 3
                                    ).toLocaleString('fa-IR')}{' '}
                                    کد آماده دیگر
                                </summary>
                                <div className="divide-y divide-amber-200 border-t border-amber-200 dark:divide-amber-900 dark:border-amber-900">
                                    {pendingRedemptions
                                        .slice(3)
                                        .map((redemption) => (
                                            <PendingRedemptionRow
                                                key={redemption.id}
                                                redemption={redemption}
                                            />
                                        ))}
                                </div>
                            </details>
                        ) : null}
                    </div>
                </section>
            ) : null}

            <section className="rounded-xl border border-sidebar-border/70 bg-background">
                <div className="flex items-center justify-between gap-3 border-b border-sidebar-border/70 p-4">
                    <div className="flex items-center gap-2">
                        <History className="size-4 text-primary" />
                        <h2 className="font-semibold">سابقه مصرف پاداش‌ها</h2>
                    </div>
                    <span className="text-xs text-muted-foreground">
                        {completedRedemptions.length.toLocaleString('fa-IR')}{' '}
                        رکورد
                    </span>
                </div>
                {completedRedemptions.length === 0 ? (
                    <div className="p-8 text-center">
                        <ReceiptText className="mx-auto size-8 text-muted-foreground" />
                        <p className="mt-3 text-sm font-medium">
                            هنوز تحویلی ثبت نشده است
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            پس از ارائه کد توسط مشتری، سابقه اینجا نمایش داده
                            می‌شود.
                        </p>
                    </div>
                ) : (
                    <div className="divide-y divide-sidebar-border/70">
                        {completedRedemptions.map((redemption) => (
                            <article
                                key={redemption.id}
                                className="grid gap-3 p-4 md:grid-cols-[1fr_auto] md:items-center"
                            >
                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="font-medium">
                                            {redemption.rewardName ??
                                                'پاداش بدون عنوان'}
                                        </p>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground">
                                            {partnerStatusLabels[
                                                redemption.status
                                            ] ?? redemption.status}
                                        </span>
                                        {redemption.purchaseConfirmed ? (
                                            <span className="flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                                                <ShoppingCart className="size-3" />
                                                خرید ثبت شده
                                            </span>
                                        ) : null}
                                    </div>
                                    <p className="mt-2 text-xs leading-6 text-muted-foreground">
                                        مشتری:{' '}
                                        {redemption.visitorName ?? 'ثبت نشده'} ·{' '}
                                        {formatPartnerDate(
                                            redemption.redeemedAt ??
                                                redemption.createdAt,
                                        )}
                                    </p>
                                </div>
                                <div className="text-xs text-muted-foreground md:text-left">
                                    <p dir="ltr">{redemption.redemptionCode}</p>
                                    {redemption.purchaseConfirmed ? (
                                        <p className="mt-1">
                                            {redemption.purchaseAmountIrr?.toLocaleString(
                                                'fa-IR',
                                            ) ?? 'مبلغ ثبت نشده'}{' '}
                                            ریال
                                        </p>
                                    ) : (
                                        <p className="mt-1">فقط تحویل پاداش</p>
                                    )}
                                </div>
                            </article>
                        ))}
                    </div>
                )}
            </section>
        </div>
    );
}
