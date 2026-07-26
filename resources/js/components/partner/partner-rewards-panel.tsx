import { Form } from '@inertiajs/react';
import {
    BadgeCheck,
    ChevronDown,
    CircleAlert,
    Gift,
    PackageCheck,
} from 'lucide-react';
import { DateTimePickerField } from '@/components/date-time-picker-field';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    formatPartnerCount,
    formatPartnerDateTimeLocal,
    partnerStatusLabels,
} from '@/components/partner/partner-dashboard-utils';
import type {
    PartnerDashboardSection,
    RewardDefinition,
} from '@/types/partner-dashboard';

export function PartnerRewardsPanel({
    rewardDefinitions,
    onNavigate,
}: {
    rewardDefinitions: RewardDefinition[];
    onNavigate: (section: PartnerDashboardSection) => void;
}) {
    const approvedCount = rewardDefinitions.filter(
        (reward) => reward.approvalStatus === 'approved',
    ).length;
    const needsRevisionCount = rewardDefinitions.filter(
        (reward) => reward.approvalStatus === 'revision_requested',
    ).length;

    return (
        <section className="rounded-xl border border-sidebar-border/70 bg-background">
            <div className="flex flex-col gap-3 border-b border-sidebar-border/70 p-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div className="flex items-center gap-2">
                        <PackageCheck className="size-5 text-primary" />
                        <h2 className="text-lg font-semibold">
                            پاداش و موجودی فروشگاه
                        </h2>
                    </div>
                    <p className="mt-2 max-w-2xl text-sm leading-7 text-muted-foreground">
                        موجودی، زمان اعتبار و وضعیت ارائه هر پیشنهاد را اینجا
                        کنترل کنید. برای جلوگیری از شلوغی، تنظیمات هر پاداش با
                        کلیک روی همان ردیف باز می‌شود.
                    </p>
                </div>
                <Button variant="outline" onClick={() => onNavigate('offers')}>
                    <Gift className="size-4" />
                    پیشنهاد جدید
                </Button>
            </div>

            <div className="grid grid-cols-2 gap-3 border-b border-sidebar-border/70 p-4 md:grid-cols-4">
                <div className="rounded-lg bg-muted/35 p-3">
                    <p className="text-xs text-muted-foreground">
                        کل پیشنهادها
                    </p>
                    <p className="mt-1 font-semibold">
                        {rewardDefinitions.length.toLocaleString('fa-IR')}
                    </p>
                </div>
                <div className="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-950/30">
                    <p className="text-xs text-emerald-800 dark:text-emerald-200">
                        تأییدشده
                    </p>
                    <p className="mt-1 font-semibold">
                        {approvedCount.toLocaleString('fa-IR')}
                    </p>
                </div>
                <div className="rounded-lg bg-amber-50 p-3 dark:bg-amber-950/30">
                    <p className="text-xs text-amber-800 dark:text-amber-200">
                        نیازمند اصلاح
                    </p>
                    <p className="mt-1 font-semibold">
                        {needsRevisionCount.toLocaleString('fa-IR')}
                    </p>
                </div>
                <div className="rounded-lg bg-muted/35 p-3">
                    <p className="text-xs text-muted-foreground">
                        مانده کل قابل تحویل
                    </p>
                    <p className="mt-1 font-semibold">
                        {rewardDefinitions
                            .reduce(
                                (total, reward) =>
                                    total + reward.inventoryRemaining,
                                0,
                            )
                            .toLocaleString('fa-IR')}
                    </p>
                </div>
            </div>

            {rewardDefinitions.length === 0 ? (
                <div className="p-8 text-center">
                    <Gift className="mx-auto size-9 text-muted-foreground" />
                    <p className="mt-3 font-medium">
                        هنوز پاداشی برای مدیریت وجود ندارد
                    </p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        ابتدا یک پیشنهاد برای کمپین ثبت کنید.
                    </p>
                    <Button
                        className="mt-4"
                        onClick={() => onNavigate('offers')}
                    >
                        ثبت اولین پیشنهاد
                    </Button>
                </div>
            ) : (
                <div className="divide-y divide-sidebar-border/70">
                    {rewardDefinitions.map((reward) => (
                        <details key={reward.id} className="group">
                            <summary className="flex cursor-pointer list-none items-start gap-3 p-4 transition hover:bg-muted/30">
                                <span
                                    className={`mt-0.5 rounded-lg p-2 ${
                                        reward.approvalStatus === 'approved'
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200'
                                            : 'bg-muted text-muted-foreground'
                                    }`}
                                >
                                    {reward.approvalStatus === 'approved' ? (
                                        <BadgeCheck className="size-4" />
                                    ) : (
                                        <CircleAlert className="size-4" />
                                    )}
                                </span>
                                <span className="min-w-0 flex-1">
                                    <span className="flex flex-wrap items-center gap-2">
                                        <span className="font-medium">
                                            {reward.name}
                                        </span>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground">
                                            {partnerStatusLabels[
                                                reward.approvalStatus
                                            ] ?? reward.approvalStatus}
                                        </span>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-[11px] text-muted-foreground">
                                            {partnerStatusLabels[
                                                reward.availabilityStatus
                                            ] ?? reward.availabilityStatus}
                                        </span>
                                    </span>
                                    <span className="mt-2 block text-xs leading-6 text-muted-foreground">
                                        مانده:{' '}
                                        {formatPartnerCount(
                                            reward.inventoryRemaining,
                                        )}{' '}
                                        · رزرو:{' '}
                                        {formatPartnerCount(
                                            reward.inventoryReserved,
                                        )}{' '}
                                        · مصرف:{' '}
                                        {formatPartnerCount(
                                            reward.inventoryRedeemed,
                                        )}
                                    </span>
                                </span>
                                <ChevronDown className="mt-2 size-4 shrink-0 text-muted-foreground transition group-open:rotate-180" />
                            </summary>

                            <div className="border-t bg-muted/15 p-4">
                                <div className="mb-4 grid gap-3 text-xs text-muted-foreground md:grid-cols-3">
                                    <p>
                                        کمپین:{' '}
                                        <span className="text-foreground">
                                            {reward.campaignName ?? 'ثبت نشده'}
                                        </span>
                                    </p>
                                    <p>
                                        گام:{' '}
                                        <span className="text-foreground">
                                            {reward.cycleStepIndex
                                                ? `${reward.cycleStepIndex.toLocaleString('fa-IR')} ـ ${reward.cycleStepLabel ?? ''}`
                                                : 'ثبت نشده'}
                                        </span>
                                    </p>
                                    <p>
                                        گزینه:{' '}
                                        <span className="text-foreground">
                                            {reward.rewardOption ??
                                                'انتخاب آزاد'}
                                        </span>
                                    </p>
                                </div>
                                {reward.reviewNotes ? (
                                    <p className="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-6 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                                        توضیح ادمین: {reward.reviewNotes}
                                    </p>
                                ) : null}
                                <Form
                                    action={`/partner/offers/${reward.id}`}
                                    method="patch"
                                    options={{ preserveScroll: true }}
                                    className="grid gap-4 md:grid-cols-2"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            {Object.keys(errors).length > 0 ? (
                                                <p
                                                    role="alert"
                                                    className="rounded-lg border border-destructive/25 bg-destructive/5 p-3 text-sm text-destructive md:col-span-2"
                                                >
                                                    تنظیمات ذخیره نشد. موارد
                                                    مشخص‌شده را اصلاح کنید.
                                                </p>
                                            ) : null}
                                            <div className="grid gap-2">
                                                <Label
                                                    htmlFor={`reward_stock_${reward.id}`}
                                                >
                                                    موجودی قابل ارائه
                                                </Label>
                                                <Input
                                                    id={`reward_stock_${reward.id}`}
                                                    name="stock_quantity"
                                                    type="number"
                                                    min="0"
                                                    defaultValue={
                                                        reward.stockQuantity ??
                                                        ''
                                                    }
                                                />
                                                <InputError
                                                    message={
                                                        errors.stock_quantity
                                                    }
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label
                                                    htmlFor={`reward_point_cost_${reward.id}`}
                                                >
                                                    هزینه امتیازی
                                                </Label>
                                                <Input
                                                    id={`reward_point_cost_${reward.id}`}
                                                    name="point_cost"
                                                    type="number"
                                                    min="0"
                                                    defaultValue={
                                                        reward.pointCost ?? ''
                                                    }
                                                />
                                                <InputError
                                                    message={errors.point_cost}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label
                                                    htmlFor={`reward_status_${reward.id}`}
                                                >
                                                    وضعیت ارائه
                                                </Label>
                                                <select
                                                    id={`reward_status_${reward.id}`}
                                                    name="availability_status"
                                                    defaultValue={
                                                        reward.availabilityStatus
                                                    }
                                                    className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                >
                                                    <option value="active">
                                                        فعال
                                                    </option>
                                                    <option value="paused">
                                                        متوقف
                                                    </option>
                                                </select>
                                                <InputError
                                                    message={
                                                        errors.availability_status
                                                    }
                                                />
                                            </div>
                                            <div className="hidden md:block" />
                                            <DateTimePickerField
                                                id={`reward_from_${reward.id}`}
                                                name="available_from"
                                                label="شروع اعتبار"
                                                defaultValue={formatPartnerDateTimeLocal(
                                                    reward.availableFrom,
                                                )}
                                                error={errors.available_from}
                                            />
                                            <DateTimePickerField
                                                id={`reward_until_${reward.id}`}
                                                name="available_until"
                                                label="پایان اعتبار"
                                                defaultValue={formatPartnerDateTimeLocal(
                                                    reward.availableUntil,
                                                )}
                                                error={errors.available_until}
                                            />
                                            <div className="grid gap-2 md:col-span-2">
                                                <Label
                                                    htmlFor={`reward_description_${reward.id}`}
                                                >
                                                    توضیح پیشنهاد
                                                </Label>
                                                <textarea
                                                    id={`reward_description_${reward.id}`}
                                                    name="description"
                                                    defaultValue={
                                                        reward.description ?? ''
                                                    }
                                                    className="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                />
                                                <InputError
                                                    message={errors.description}
                                                />
                                            </div>
                                            <div className="grid gap-2 md:col-span-2">
                                                <Label
                                                    htmlFor={`reward_terms_${reward.id}`}
                                                >
                                                    شرایط مصرف
                                                </Label>
                                                <textarea
                                                    id={`reward_terms_${reward.id}`}
                                                    name="terms"
                                                    defaultValue={
                                                        reward.terms ?? ''
                                                    }
                                                    className="min-h-20 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                />
                                                <InputError
                                                    message={errors.terms}
                                                />
                                            </div>
                                            <div className="flex justify-end md:col-span-2">
                                                <Button
                                                    size="sm"
                                                    disabled={processing}
                                                >
                                                    {processing
                                                        ? 'در حال ذخیره...'
                                                        : 'ذخیره تنظیمات پاداش'}
                                                </Button>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            </div>
                        </details>
                    ))}
                </div>
            )}
        </section>
    );
}
