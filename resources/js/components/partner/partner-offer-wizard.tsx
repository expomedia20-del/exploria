import { Form } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    BadgePercent,
    Check,
    CircleAlert,
    Gift,
    Layers3,
    ListChecks,
    Send,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    describeRewardOption,
    partnerStatusLabels,
} from '@/components/partner/partner-dashboard-utils';
import type {
    MissionPlanStep,
    Partner,
    PartnerDashboardProps,
    RewardDefinition,
} from '@/types/partner-dashboard';

const wizardSteps = [
    { index: 1, title: 'انتخاب گام کمپین', icon: Layers3 },
    { index: 2, title: 'نوع پاداش', icon: Gift },
    { index: 3, title: 'ظرفیت و شرایط', icon: ListChecks },
    { index: 4, title: 'بازبینی و ارسال', icon: Send },
];

function OfferStatusList({
    rewardDefinitions,
}: {
    rewardDefinitions: RewardDefinition[];
}) {
    return (
        <section className="rounded-xl border border-sidebar-border/70 bg-background">
            <div className="flex items-center justify-between gap-3 border-b border-sidebar-border/70 p-4">
                <div>
                    <h2 className="font-semibold">پیگیری پیشنهادهای من</h2>
                    <p className="mt-1 text-xs text-muted-foreground">
                        نتیجه بررسی ادمین و درخواست اصلاح در این بخش دیده
                        می‌شود.
                    </p>
                </div>
                <span className="rounded-full bg-muted px-3 py-1 text-xs">
                    {rewardDefinitions.length.toLocaleString('fa-IR')} پیشنهاد
                </span>
            </div>
            {rewardDefinitions.length === 0 ? (
                <div className="p-6 text-center">
                    <BadgePercent className="mx-auto size-8 text-muted-foreground" />
                    <p className="mt-3 text-sm font-medium">
                        هنوز پیشنهادی ثبت نشده است
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground">
                        فرم بالا اولین پیشنهاد شما را قدم‌به‌قدم آماده می‌کند.
                    </p>
                </div>
            ) : (
                <div className="divide-y divide-sidebar-border/70">
                    {rewardDefinitions.map((reward) => (
                        <article
                            key={reward.id}
                            className="grid gap-3 p-4 md:grid-cols-[1fr_auto] md:items-center"
                        >
                            <div className="min-w-0">
                                <div className="flex flex-wrap items-center gap-2">
                                    <p className="font-medium">{reward.name}</p>
                                    <span
                                        className={`rounded-full px-2.5 py-1 text-[11px] ${
                                            reward.approvalStatus === 'approved'
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                                : reward.approvalStatus ===
                                                    'revision_requested'
                                                  ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                                  : reward.approvalStatus ===
                                                      'rejected'
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200'
                                                    : 'bg-muted text-muted-foreground'
                                        }`}
                                    >
                                        {partnerStatusLabels[
                                            reward.approvalStatus
                                        ] ?? reward.approvalStatus}
                                    </span>
                                </div>
                                <p className="mt-2 text-xs leading-6 text-muted-foreground">
                                    {reward.campaignName ?? 'کمپین جاری'} · گام{' '}
                                    {reward.cycleStepIndex?.toLocaleString(
                                        'fa-IR',
                                    ) ?? 'ثبت نشده'}{' '}
                                    · {reward.rewardOption ?? 'انتخاب آزاد'}
                                </p>
                                {reward.reviewNotes ? (
                                    <p className="mt-2 rounded-md bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                                        توضیح بررسی: {reward.reviewNotes}
                                    </p>
                                ) : null}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                ظرفیت:{' '}
                                {reward.stockQuantity?.toLocaleString(
                                    'fa-IR',
                                ) ?? 'نامحدود'}
                            </p>
                        </article>
                    ))}
                </div>
            )}
        </section>
    );
}

export function PartnerOfferWizard({
    partner,
    proposalContext,
    rewardDefinitions,
}: {
    partner: Partner;
    proposalContext: PartnerDashboardProps['proposalContext'];
    rewardDefinitions: RewardDefinition[];
}) {
    const firstStepIndex = proposalContext.missionPlan[0]?.index ?? '';
    const [wizardStep, setWizardStep] = useState(1);
    const [selectedStepIndex, setSelectedStepIndex] = useState(
        String(firstStepIndex),
    );
    const selectedStep = useMemo(
        () =>
            proposalContext.missionPlan.find(
                (step) => String(step.index) === selectedStepIndex,
            ) ?? null,
        [proposalContext.missionPlan, selectedStepIndex],
    );
    const selectedTier = useMemo(
        () =>
            proposalContext.rewardTiers.find(
                (tier) => tier.tierKey === selectedStep?.rewardTier,
            ) ?? null,
        [proposalContext.rewardTiers, selectedStep?.rewardTier],
    );
    const [selectedRewardOption, setSelectedRewardOption] = useState(
        selectedTier?.options[0] ?? '',
    );
    const [rewardType, setRewardType] = useState('discount');
    const [offerName, setOfferName] = useState('');
    const [pointCost, setPointCost] = useState('');
    const [stockQuantity, setStockQuantity] = useState('');
    const [description, setDescription] = useState('');
    const [terms, setTerms] = useState('');

    const selectMissionStep = (step: MissionPlanStep) => {
        const tier = proposalContext.rewardTiers.find(
            (item) => item.tierKey === step.rewardTier,
        );

        setSelectedStepIndex(String(step.index));
        setSelectedRewardOption(tier?.options[0] ?? '');
    };
    const canContinue =
        wizardStep === 1
            ? Boolean(selectedStep)
            : wizardStep === 3
              ? offerName.trim().length > 0
              : true;

    return (
        <div className="grid gap-4">
            <section className="rounded-xl border border-sidebar-border/70 bg-background">
                <div className="border-b border-sidebar-border/70 p-4">
                    <div className="flex items-center gap-2">
                        <BadgePercent className="size-5 text-primary" />
                        <div>
                            <h2 className="text-lg font-semibold">
                                ثبت پیشنهاد/تخفیف جدید
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                ساخت پیشنهاد در ۴ گام؛ پس از ارسال، ادمین آن را
                                بررسی می‌کند.
                            </p>
                        </div>
                    </div>
                    <ol className="mt-5 grid grid-cols-2 gap-2 lg:grid-cols-4">
                        {wizardSteps.map((step) => {
                            const Icon = step.icon;
                            const isActive = wizardStep === step.index;
                            const isDone = wizardStep > step.index;

                            return (
                                <li key={step.index}>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setWizardStep(step.index)
                                        }
                                        className={`flex w-full items-center gap-2 rounded-lg border p-2.5 text-right text-xs transition ${
                                            isActive
                                                ? 'border-primary bg-primary/10 text-primary'
                                                : isDone
                                                  ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200'
                                                  : 'border-sidebar-border/70 text-muted-foreground hover:bg-muted/40'
                                        }`}
                                    >
                                        <span
                                            className={`flex size-7 shrink-0 items-center justify-center rounded-full ${
                                                isActive
                                                    ? 'bg-primary text-primary-foreground'
                                                    : isDone
                                                      ? 'bg-emerald-600 text-white'
                                                      : 'bg-muted'
                                            }`}
                                        >
                                            {isDone ? (
                                                <Check className="size-3.5" />
                                            ) : (
                                                <Icon className="size-3.5" />
                                            )}
                                        </span>
                                        <span>{step.title}</span>
                                    </button>
                                </li>
                            );
                        })}
                    </ol>
                </div>

                {!proposalContext.campaign ? (
                    <div className="p-6">
                        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                            <div className="flex items-center gap-2 font-medium">
                                <CircleAlert className="size-4" />
                                کمپین قابل انتخاب پیدا نشد
                            </div>
                            <p className="mt-2 leading-7">
                                برای این مکان هنوز کمپین در حال تنظیم وجود
                                ندارد. پس از اتصال کمپین توسط ادمین، فرم ساخت
                                پیشنهاد فعال می‌شود.
                            </p>
                        </div>
                    </div>
                ) : (
                    <Form
                        action="/partner/offers"
                        method="post"
                        options={{ preserveScroll: true }}
                        className="p-4"
                        autoComplete="off"
                    >
                        {({ processing, errors }) => (
                            <>
                                <input
                                    type="hidden"
                                    name="campaign_id"
                                    value={proposalContext.campaign?.id ?? ''}
                                />
                                <input
                                    type="hidden"
                                    name="cycle_step_index"
                                    value={selectedStepIndex}
                                />
                                <input
                                    type="hidden"
                                    name="cycle_step_label"
                                    value={selectedStep?.userStep ?? ''}
                                />
                                <input
                                    type="hidden"
                                    name="reward_tier"
                                    value={selectedStep?.rewardTier ?? ''}
                                />

                                {Object.keys(errors).length > 0 ? (
                                    <div
                                        role="alert"
                                        className="mb-4 rounded-lg border border-destructive/25 bg-destructive/5 p-3 text-sm text-destructive"
                                    >
                                        پیشنهاد ارسال نشد. اطلاعات مشخص‌شده را
                                        اصلاح و دوباره تلاش کنید.
                                    </div>
                                ) : null}

                                <div
                                    className={
                                        wizardStep === 1
                                            ? 'grid gap-4'
                                            : 'hidden'
                                    }
                                >
                                    <div className="rounded-lg bg-primary/5 p-3">
                                        <p className="text-xs text-muted-foreground">
                                            کمپین انتخاب‌شده
                                        </p>
                                        <p className="mt-1 font-medium">
                                            {proposalContext.campaign?.name ??
                                                'کمپین جاری'}
                                        </p>
                                    </div>
                                    {proposalContext.missionPlan.length ===
                                    0 ? (
                                        <p className="rounded-lg border p-4 text-sm text-muted-foreground">
                                            چرخه مأموریت این کمپین هنوز آماده
                                            نشده است.
                                        </p>
                                    ) : (
                                        <fieldset>
                                            <legend className="text-sm font-medium">
                                                پیشنهاد شما به کدام گام کمک
                                                می‌کند؟
                                            </legend>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                یک گام را انتخاب کنید؛ سطح پاداش
                                                آن به‌صورت خودکار اعمال می‌شود.
                                            </p>
                                            <div className="mt-3 grid gap-2 md:grid-cols-2">
                                                {proposalContext.missionPlan.map(
                                                    (step) => {
                                                        const tier =
                                                            proposalContext.rewardTiers.find(
                                                                (item) =>
                                                                    item.tierKey ===
                                                                    step.rewardTier,
                                                            );
                                                        const selected =
                                                            selectedStepIndex ===
                                                            String(step.index);

                                                        return (
                                                            <button
                                                                key={step.index}
                                                                type="button"
                                                                onClick={() =>
                                                                    selectMissionStep(
                                                                        step,
                                                                    )
                                                                }
                                                                className={`rounded-lg border p-3 text-right transition ${
                                                                    selected
                                                                        ? 'border-primary bg-primary/10 ring-1 ring-primary/20'
                                                                        : 'border-sidebar-border/70 hover:border-primary/30 hover:bg-muted/30'
                                                                }`}
                                                            >
                                                                <span className="flex items-center justify-between gap-2">
                                                                    <span className="text-sm font-medium">
                                                                        گام{' '}
                                                                        {step.index.toLocaleString(
                                                                            'fa-IR',
                                                                        )}
                                                                        :{' '}
                                                                        {
                                                                            step.userStep
                                                                        }
                                                                    </span>
                                                                    {selected ? (
                                                                        <Check className="size-4 text-primary" />
                                                                    ) : null}
                                                                </span>
                                                                <span className="mt-2 block text-xs leading-6 text-muted-foreground">
                                                                    سطح:{' '}
                                                                    {tier?.level ??
                                                                        step.rewardTier}{' '}
                                                                    ·{' '}
                                                                    {
                                                                        step.routeIntent
                                                                    }
                                                                </span>
                                                            </button>
                                                        );
                                                    },
                                                )}
                                            </div>
                                        </fieldset>
                                    )}
                                    <InputError
                                        message={errors.cycle_step_index}
                                    />
                                    <InputError message={errors.reward_tier} />
                                </div>

                                <div
                                    className={
                                        wizardStep === 2
                                            ? 'grid gap-5'
                                            : 'hidden'
                                    }
                                >
                                    <div className="rounded-lg bg-muted/40 p-3 text-sm">
                                        <span className="text-muted-foreground">
                                            گام انتخابی:{' '}
                                        </span>
                                        <span className="font-medium">
                                            {selectedStep?.userStep ??
                                                'انتخاب نشده'}
                                        </span>
                                        <span className="mx-2 text-muted-foreground">
                                            ·
                                        </span>
                                        <span className="text-muted-foreground">
                                            سطح:{' '}
                                        </span>
                                        <span className="font-medium">
                                            {selectedTier?.level ?? '-'}
                                        </span>
                                    </div>
                                    <fieldset>
                                        <legend className="text-sm font-medium">
                                            گزینه پاداش پیشنهادی
                                        </legend>
                                        <div className="mt-3 grid gap-2 md:grid-cols-2">
                                            <label
                                                className={`cursor-pointer rounded-lg border p-3 text-sm ${
                                                    selectedRewardOption === ''
                                                        ? 'border-primary bg-primary/10'
                                                        : 'border-sidebar-border/70'
                                                }`}
                                            >
                                                <input
                                                    type="radio"
                                                    name="reward_option"
                                                    value=""
                                                    checked={
                                                        selectedRewardOption ===
                                                        ''
                                                    }
                                                    onChange={() =>
                                                        setSelectedRewardOption(
                                                            '',
                                                        )
                                                    }
                                                    className="ml-2"
                                                />
                                                انتخاب آزاد با بررسی ادمین
                                            </label>
                                            {(selectedTier?.options ?? []).map(
                                                (option) => (
                                                    <label
                                                        key={option}
                                                        className={`cursor-pointer rounded-lg border p-3 text-sm ${
                                                            selectedRewardOption ===
                                                            option
                                                                ? 'border-primary bg-primary/10'
                                                                : 'border-sidebar-border/70'
                                                        }`}
                                                    >
                                                        <input
                                                            type="radio"
                                                            name="reward_option"
                                                            value={option}
                                                            checked={
                                                                selectedRewardOption ===
                                                                option
                                                            }
                                                            onChange={() =>
                                                                setSelectedRewardOption(
                                                                    option,
                                                                )
                                                            }
                                                            className="ml-2"
                                                        />
                                                        {option}
                                                    </label>
                                                ),
                                            )}
                                        </div>
                                        <p className="mt-3 rounded-lg bg-muted/35 p-3 text-xs leading-6 text-muted-foreground">
                                            {describeRewardOption(
                                                selectedRewardOption,
                                                selectedStep,
                                            )}
                                        </p>
                                        <InputError
                                            message={errors.reward_option}
                                        />
                                    </fieldset>
                                    <div className="grid gap-2">
                                        <Label htmlFor="reward_type">
                                            جنس پیشنهاد فروشگاه
                                        </Label>
                                        <select
                                            id="reward_type"
                                            name="reward_type"
                                            required
                                            value={rewardType}
                                            onChange={(event) =>
                                                setRewardType(
                                                    event.target.value,
                                                )
                                            }
                                            className="h-10 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        >
                                            <option value="discount">
                                                تخفیف
                                            </option>
                                            <option value="partner_coupon">
                                                کوپن فروشگاهی
                                            </option>
                                            <option value="gift">هدیه</option>
                                            <option value="service_credit">
                                                اعتبار خدمات
                                            </option>
                                            <option value="sponsor_reward">
                                                پاداش تأمین‌شده توسط اسپانسر
                                            </option>
                                        </select>
                                        <InputError
                                            message={errors.reward_type}
                                        />
                                    </div>
                                </div>

                                <div
                                    className={
                                        wizardStep === 3
                                            ? 'grid gap-4 md:grid-cols-2'
                                            : 'hidden'
                                    }
                                >
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="offer_name">
                                            عنوان پیشنهادی
                                        </Label>
                                        <Input
                                            id="offer_name"
                                            name="name"
                                            required
                                            value={offerName}
                                            onChange={(event) =>
                                                setOfferName(event.target.value)
                                            }
                                            placeholder={
                                                selectedRewardOption
                                                    ? `${selectedRewardOption} - پیشنهاد ${partner.name}`
                                                    : 'عنوان کالا، خدمت یا تخفیف پیشنهادی'
                                            }
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="stock_quantity">
                                            ظرفیت قابل ارائه
                                        </Label>
                                        <Input
                                            id="stock_quantity"
                                            name="stock_quantity"
                                            type="number"
                                            min="1"
                                            inputMode="numeric"
                                            value={stockQuantity}
                                            onChange={(event) =>
                                                setStockQuantity(
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="مثلاً ۵۰ عدد"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            خالی بماند یعنی ظرفیت نامحدود است.
                                        </p>
                                        <InputError
                                            message={errors.stock_quantity}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="point_cost">
                                            هزینه امتیازی
                                        </Label>
                                        <Input
                                            id="point_cost"
                                            name="point_cost"
                                            type="number"
                                            min="0"
                                            inputMode="numeric"
                                            value={pointCost}
                                            onChange={(event) =>
                                                setPointCost(event.target.value)
                                            }
                                            placeholder="اختیاری"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            ادمین می‌تواند این مقدار را هنگام
                                            تأیید نهایی کند.
                                        </p>
                                        <InputError
                                            message={errors.point_cost}
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="description">
                                            پیشنهاد دقیقاً شامل چیست؟
                                        </Label>
                                        <textarea
                                            id="description"
                                            name="description"
                                            value={description}
                                            onChange={(event) =>
                                                setDescription(
                                                    event.target.value,
                                                )
                                            }
                                            className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            placeholder="کالا، خدمت، مقدار تخفیف و ارزش واقعی پیشنهاد را روشن بنویسید."
                                        />
                                        <InputError
                                            message={errors.description}
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label htmlFor="terms">
                                            شرایط مصرف
                                        </Label>
                                        <textarea
                                            id="terms"
                                            name="terms"
                                            value={terms}
                                            onChange={(event) =>
                                                setTerms(event.target.value)
                                            }
                                            className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            placeholder="زمان معتبر، محدودیت هر کاربر و شرایطی که مشتری باید بداند."
                                        />
                                        <InputError message={errors.terms} />
                                    </div>
                                </div>

                                <div
                                    className={
                                        wizardStep === 4
                                            ? 'grid gap-4'
                                            : 'hidden'
                                    }
                                >
                                    <div className="rounded-xl border border-primary/25 bg-primary/5 p-4">
                                        <p className="text-xs text-muted-foreground">
                                            پیش‌نمایش پیشنهاد
                                        </p>
                                        <h3 className="mt-2 text-lg font-semibold">
                                            {offerName ||
                                                'عنوان پیشنهاد هنوز ثبت نشده است'}
                                        </h3>
                                        <dl className="mt-4 grid gap-3 text-sm md:grid-cols-2">
                                            <div>
                                                <dt className="text-xs text-muted-foreground">
                                                    کمپین و گام
                                                </dt>
                                                <dd className="mt-1">
                                                    {proposalContext.campaign
                                                        ?.name ??
                                                        'کمپین جاری'}{' '}
                                                    ·{' '}
                                                    {selectedStep?.userStep ??
                                                        '-'}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">
                                                    نوع و گزینه
                                                </dt>
                                                <dd className="mt-1">
                                                    {selectedRewardOption ||
                                                        'انتخاب آزاد'}{' '}
                                                    ·{' '}
                                                    {rewardType === 'discount'
                                                        ? 'تخفیف'
                                                        : rewardType ===
                                                            'partner_coupon'
                                                          ? 'کوپن فروشگاهی'
                                                          : rewardType ===
                                                              'gift'
                                                            ? 'هدیه'
                                                            : rewardType ===
                                                                'service_credit'
                                                              ? 'اعتبار خدمات'
                                                              : 'پاداش اسپانسری'}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">
                                                    ظرفیت
                                                </dt>
                                                <dd className="mt-1">
                                                    {stockQuantity
                                                        ? Number(
                                                              stockQuantity,
                                                          ).toLocaleString(
                                                              'fa-IR',
                                                          )
                                                        : 'نامحدود'}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-muted-foreground">
                                                    هزینه امتیازی
                                                </dt>
                                                <dd className="mt-1">
                                                    {pointCost
                                                        ? Number(
                                                              pointCost,
                                                          ).toLocaleString(
                                                              'fa-IR',
                                                          )
                                                        : 'در بررسی ادمین'}
                                                </dd>
                                            </div>
                                        </dl>
                                        {description ? (
                                            <p className="mt-4 border-t pt-4 text-sm leading-7 text-muted-foreground">
                                                {description}
                                            </p>
                                        ) : null}
                                    </div>
                                    <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-6 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                                        ارسال به معنی انتشار فوری نیست. ادمین
                                        تناسب پیشنهاد، ظرفیت و شرایط مصرف را
                                        بررسی می‌کند و نتیجه در همین صفحه نشان
                                        داده می‌شود.
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={
                                            processing ||
                                            !selectedStep ||
                                            !offerName.trim()
                                        }
                                        className="w-full sm:w-auto"
                                    >
                                        <Send className="size-4" />
                                        {processing
                                            ? 'در حال ارسال...'
                                            : 'ارسال پیشنهاد برای تأیید'}
                                    </Button>
                                </div>

                                <div className="mt-6 flex items-center justify-between gap-3 border-t pt-4">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={wizardStep === 1}
                                        onClick={() =>
                                            setWizardStep((current) =>
                                                Math.max(1, current - 1),
                                            )
                                        }
                                    >
                                        <ArrowRight className="size-4" />
                                        مرحله قبل
                                    </Button>
                                    {wizardStep < wizardSteps.length ? (
                                        <Button
                                            type="button"
                                            disabled={!canContinue}
                                            onClick={() =>
                                                setWizardStep((current) =>
                                                    Math.min(
                                                        wizardSteps.length,
                                                        current + 1,
                                                    ),
                                                )
                                            }
                                        >
                                            مرحله بعد
                                            <ArrowLeft className="size-4" />
                                        </Button>
                                    ) : null}
                                </div>
                            </>
                        )}
                    </Form>
                )}
            </section>

            <OfferStatusList rewardDefinitions={rewardDefinitions} />
        </div>
    );
}
