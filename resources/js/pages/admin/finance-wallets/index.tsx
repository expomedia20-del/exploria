import { Form, Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeDollarSign,
    BookOpenCheck,
    ClipboardList,
    Plus,
    ShieldAlert,
    WalletCards,
} from 'lucide-react';
import { Button } from '@/components/ui/button';

type Summary = {
    accountsCount: number;
    creditTotal: number;
    debitTotal: number;
    committedTotal: number;
    netBalance: number;
    contractsReady: number;
};

type FinancialAccount = {
    id: string;
    key: string;
    type: string;
    typeLabel: string;
    ownerName: string;
    currency: string;
    status: string;
    creditTotal: number;
    debitTotal: number;
    balance: number;
    entriesCount: number;
    walletRole: string | null;
};

type LedgerEntry = {
    id: string;
    accountName: string;
    accountType: string;
    entryType: string;
    entryTypeLabel: string;
    direction: 'credit' | 'debit';
    amount: number;
    currency: string;
    status: string;
    contractType: string | null;
    description: string | null;
    occurredOn: string | null;
    createdBy: string | null;
};

type ContractTemplate = {
    id: string;
    code: string;
    title: string;
    partyType: string;
    partyTypeLabel: string;
    pricingModel: string;
    baseAmount: number;
    platformFeePercent: number | null;
    settlementTerms: string | null;
    scopeSummary: string | null;
    status: string;
};

type FormOption = {
    value?: string;
    code?: string;
    id?: string;
    label: string;
    type?: string;
};

type Props = {
    summary: Summary;
    accounts: FinancialAccount[];
    ledgerEntries: LedgerEntry[];
    contractTemplates: ContractTemplate[];
    formOptions: {
        accounts: FormOption[];
        entryTypes: FormOption[];
        contractTypes: FormOption[];
    };
    boundaries: string[];
    canManageFinanceLedger: boolean;
};

const accountSurfaceClassName = [
    'border-emerald-200 bg-emerald-50/65',
    'border-sky-200 bg-sky-50/65',
    'border-amber-200 bg-amber-50/65',
    'border-rose-200 bg-rose-50/60',
    'border-indigo-200 bg-indigo-50/60',
];

const templateSurfaceClassName = [
    'border-cyan-200 bg-cyan-50/60',
    'border-emerald-200 bg-emerald-50/60',
    'border-amber-200 bg-amber-50/60',
    'border-indigo-200 bg-indigo-50/60',
];

function money(value: number) {
    return `${value.toLocaleString('fa-IR')} ریال`;
}

function directionLabel(direction: LedgerEntry['direction']) {
    return direction === 'credit' ? 'ورودی/بستانکار' : 'خروجی/بدهکار';
}

export default function FinanceWalletIndex({
    summary,
    accounts,
    ledgerEntries,
    contractTemplates,
    formOptions,
    boundaries,
    canManageFinanceLedger,
}: Props) {
    return (
        <>
            <Head title="اقتصاد و کیف پول‌های اکسپلوریا" />

            <main
                className="space-y-4 bg-[linear-gradient(180deg,#f8fafc_0%,#f3f8f2_46%,#f8fafc_100%)] p-4"
                dir="rtl"
            >
                <section className="rounded-lg border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-cyan-50 p-5 shadow-sm">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p className="text-sm text-emerald-800">
                                دفترکل داخلی، کیف پول نقش‌ها و تیپ قراردادها
                            </p>
                            <h1 className="mt-2 text-3xl font-semibold">
                                اقتصاد عملیاتی اکسپلوریا
                            </h1>
                            <p className="mt-3 max-w-4xl text-sm leading-7 text-muted-foreground">
                                این صفحه پرداخت واقعی انجام نمی‌دهد؛ تعهدها،
                                بودجه‌ها، مصرف پاداش، کارمزد، سهم مکان و تسویه
                                شریک را به‌صورت دفترکل داخلی ثبت می‌کند تا مدل
                                درآمدی قبل از اتصال به درگاه یا حسابداری روشن
                                شود.
                            </p>
                        </div>
                        <Link
                            href="/admin/commercialization"
                            className="inline-flex h-10 items-center gap-2 rounded-md border border-emerald-300 bg-white/70 px-3 text-sm font-medium"
                        >
                            خروجی فروش
                            <ArrowLeft className="size-4" />
                        </Link>
                    </div>

                    <div className="mt-5 grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                        {[
                            ['حساب‌ها', summary.accountsCount],
                            ['تعهدها', money(summary.committedTotal)],
                            ['ورودی‌ها', money(summary.creditTotal)],
                            ['خروجی‌ها', money(summary.debitTotal)],
                            ['مانده خالص', money(summary.netBalance)],
                            ['قرارداد آماده', summary.contractsReady],
                        ].map(([label, value]) => (
                            <div
                                key={label}
                                className="rounded-md border border-white/80 bg-white/70 p-3 shadow-sm"
                            >
                                <p className="text-xs text-muted-foreground">
                                    {label}
                                </p>
                                <strong className="mt-1 block text-lg">
                                    {value}
                                </strong>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="grid gap-3 xl:grid-cols-[1.2fr_0.8fr]">
                    <div className="rounded-lg border border-sky-200 bg-gradient-to-br from-sky-50/80 via-white to-indigo-50/60 p-4 shadow-sm">
                        <div className="flex items-center gap-2">
                            <WalletCards className="size-5 text-sky-700" />
                            <h2 className="text-xl font-semibold">
                                کیف پول نقش‌ها
                            </h2>
                        </div>
                        <div className="mt-4 grid gap-3 md:grid-cols-2">
                            {accounts.map((account, index) => (
                                <article
                                    key={account.id}
                                    className={`rounded-md border border-t-4 p-3 shadow-sm ${accountSurfaceClassName[index % accountSurfaceClassName.length]}`}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-xs text-muted-foreground">
                                                {account.typeLabel}
                                            </p>
                                            <h3 className="mt-1 font-semibold">
                                                {account.ownerName}
                                            </h3>
                                        </div>
                                        <span className="rounded-full bg-white/70 px-2.5 py-1 text-xs font-medium">
                                            {account.status}
                                        </span>
                                    </div>
                                    <div className="mt-3 grid gap-2 text-sm">
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">
                                                ورودی
                                            </span>
                                            <strong>
                                                {money(account.creditTotal)}
                                            </strong>
                                        </div>
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">
                                                خروجی
                                            </span>
                                            <strong>
                                                {money(account.debitTotal)}
                                            </strong>
                                        </div>
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">
                                                مانده
                                            </span>
                                            <strong>
                                                {money(account.balance)}
                                            </strong>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-lg border border-amber-200 bg-gradient-to-br from-amber-50/85 via-white to-rose-50/50 p-4 shadow-sm">
                        <div className="flex items-center gap-2">
                            <Plus className="size-5 text-amber-700" />
                            <h2 className="text-xl font-semibold">
                                ثبت تراکنش دفترکل
                            </h2>
                        </div>
                        <p className="mt-2 text-sm leading-7 text-muted-foreground">
                            برای MVP، این ثبت فقط دفترکل داخلی است؛ پرداخت واقعی
                            یا تسویه بانکی انجام نمی‌شود.
                        </p>

                        {canManageFinanceLedger ? (
                            <Form
                                action="/admin/finance-wallets/ledger"
                                method="post"
                                options={{ preserveScroll: true }}
                            >
                                {({ processing }) => (
                                    <div className="mt-4 grid gap-3">
                                        <label className="grid gap-1 text-sm font-medium">
                                            حساب
                                            <select
                                                name="financial_account_id"
                                                required
                                                className="h-10 rounded-md border border-input bg-white px-3 text-sm"
                                            >
                                                {formOptions.accounts.map(
                                                    (account) => (
                                                        <option
                                                            key={account.id}
                                                            value={account.id}
                                                        >
                                                            {account.label}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </label>
                                        <div className="grid gap-3 md:grid-cols-2">
                                            <label className="grid gap-1 text-sm font-medium">
                                                نوع تراکنش
                                                <select
                                                    name="entry_type"
                                                    className="h-10 rounded-md border border-input bg-white px-3 text-sm"
                                                >
                                                    {formOptions.entryTypes.map(
                                                        (item) => (
                                                            <option
                                                                key={item.value}
                                                                value={
                                                                    item.value
                                                                }
                                                            >
                                                                {item.label}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                            </label>
                                            <label className="grid gap-1 text-sm font-medium">
                                                جهت
                                                <select
                                                    name="direction"
                                                    className="h-10 rounded-md border border-input bg-white px-3 text-sm"
                                                >
                                                    <option value="credit">
                                                        ورودی/بستانکار
                                                    </option>
                                                    <option value="debit">
                                                        خروجی/بدهکار
                                                    </option>
                                                </select>
                                            </label>
                                        </div>
                                        <div className="grid gap-3 md:grid-cols-2">
                                            <label className="grid gap-1 text-sm font-medium">
                                                مبلغ ریالی
                                                <input
                                                    name="amount"
                                                    type="number"
                                                    min="1"
                                                    required
                                                    className="h-10 rounded-md border border-input bg-white px-3 text-sm"
                                                    placeholder="مثلا 25000000"
                                                />
                                            </label>
                                            <label className="grid gap-1 text-sm font-medium">
                                                تاریخ
                                                <input
                                                    name="occurred_on"
                                                    type="date"
                                                    className="h-10 rounded-md border border-input bg-white px-3 text-sm"
                                                />
                                            </label>
                                        </div>
                                        <label className="grid gap-1 text-sm font-medium">
                                            تیپ قرارداد
                                            <select
                                                name="contract_type"
                                                className="h-10 rounded-md border border-input bg-white px-3 text-sm"
                                            >
                                                <option value="">
                                                    بدون اتصال قرارداد
                                                </option>
                                                {formOptions.contractTypes.map(
                                                    (item) => (
                                                        <option
                                                            key={item.code}
                                                            value={item.code}
                                                        >
                                                            {item.label}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </label>
                                        <label className="grid gap-1 text-sm font-medium">
                                            توضیح
                                            <textarea
                                                name="description"
                                                className="min-h-20 rounded-md border border-input bg-white px-3 py-2 text-sm"
                                                placeholder="مثلا تعهد بودجه اسپانسر برای مسیر خانواده یا مصرف پاداش کافه"
                                            />
                                        </label>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="justify-self-start"
                                        >
                                            <BadgeDollarSign className="size-4" />
                                            {processing
                                                ? 'در حال ثبت'
                                                : 'ثبت در دفترکل'}
                                        </Button>
                                    </div>
                                )}
                            </Form>
                        ) : (
                            <div className="mt-4 rounded-md border border-amber-200 bg-white/70 p-3 text-sm leading-7 text-amber-950">
                                نقش شما فقط مجاز به مشاهده دفترکل مالی است.
                            </div>
                        )}
                    </div>
                </section>

                <section className="rounded-lg border border-emerald-200 bg-gradient-to-br from-white via-emerald-50/55 to-cyan-50/55 p-4 shadow-sm">
                    <div className="flex items-center gap-2">
                        <BookOpenCheck className="size-5 text-emerald-700" />
                        <h2 className="text-xl font-semibold">
                            تیپ قراردادها و مدل پرداخت
                        </h2>
                    </div>
                    <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        {contractTemplates.map((template, index) => (
                            <article
                                key={template.id}
                                className={`rounded-md border border-t-4 p-3 shadow-sm ${templateSurfaceClassName[index % templateSurfaceClassName.length]}`}
                            >
                                <p className="text-xs text-muted-foreground">
                                    {template.partyTypeLabel} ·{' '}
                                    {template.status}
                                </p>
                                <h3 className="mt-1 font-semibold">
                                    {template.title}
                                </h3>
                                <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                    {template.scopeSummary}
                                </p>
                                <div className="mt-3 grid gap-2 text-sm">
                                    <div className="flex justify-between gap-3">
                                        <span className="text-muted-foreground">
                                            پایه
                                        </span>
                                        <strong>
                                            {money(template.baseAmount)}
                                        </strong>
                                    </div>
                                    <div className="flex justify-between gap-3">
                                        <span className="text-muted-foreground">
                                            کارمزد
                                        </span>
                                        <strong>
                                            {template.platformFeePercent ?? 0}٪
                                        </strong>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="grid gap-3 xl:grid-cols-[1.2fr_0.8fr]">
                    <div className="rounded-lg border border-indigo-200 bg-indigo-50/55 p-4 shadow-sm">
                        <div className="flex items-center gap-2">
                            <ClipboardList className="size-5 text-indigo-700" />
                            <h2 className="text-xl font-semibold">
                                آخرین تراکنش‌ها
                            </h2>
                        </div>
                        <div className="mt-4 grid gap-2">
                            {ledgerEntries.length > 0 ? (
                                ledgerEntries.map((entry) => (
                                    <div
                                        key={entry.id}
                                        className="grid gap-2 rounded-md border border-white/80 bg-white/75 p-3 text-sm md:grid-cols-[1fr_auto]"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {entry.accountName} ·{' '}
                                                {entry.entryTypeLabel}
                                            </p>
                                            <p className="mt-1 text-muted-foreground">
                                                {entry.description ??
                                                    'بدون توضیح'}
                                            </p>
                                        </div>
                                        <div className="text-left">
                                            <strong
                                                className={
                                                    entry.direction === 'credit'
                                                        ? 'text-emerald-700'
                                                        : 'text-rose-700'
                                                }
                                            >
                                                {money(entry.amount)}
                                            </strong>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {directionLabel(
                                                    entry.direction,
                                                )}
                                            </p>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-md border border-dashed border-indigo-200 bg-white/65 p-4 text-sm text-muted-foreground">
                                    هنوز تراکنشی ثبت نشده است.
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-lg border border-rose-200 bg-rose-50/55 p-4 shadow-sm">
                        <div className="flex items-center gap-2">
                            <ShieldAlert className="size-5 text-rose-700" />
                            <h2 className="text-xl font-semibold">
                                مرزهای مالی MVP
                            </h2>
                        </div>
                        <div className="mt-4 space-y-2">
                            {boundaries.map((item) => (
                                <div
                                    key={item}
                                    className="rounded-md bg-white/70 p-3 text-sm leading-7 text-rose-950"
                                >
                                    {item}
                                </div>
                            ))}
                        </div>
                        <div className="mt-4 rounded-md border border-rose-200 bg-white/70 p-3 text-sm leading-7 text-muted-foreground">
                            اتصال به درگاه پرداخت، صدور فاکتور رسمی، مالیات،
                            تسویه چندطرفه و پرداخت نقدی کاربر، مرحله بعد از
                            تایید حقوقی و مالی است.
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
