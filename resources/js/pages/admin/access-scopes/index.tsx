import { Form, Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import {
    AlertTriangle,
    CheckCircle2,
    ClipboardCheck,
    Layers3,
    Plus,
    ShieldCheck,
    UserCog,
    UsersRound,
    XCircle,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

type UserOption = {
    id: number;
    name: string;
    email: string;
    role: string | null;
    roleLabel: string;
    kind: string;
    kindLabel: string;
    isStressDemo: boolean;
};

type RoleOption = {
    key: string;
    label: string;
    defaultScope: string;
    governance: RoleGovernance;
};

type ScopeOption = {
    id: string | null;
    label: string;
};

type AccessScope = {
    id: string;
    roleKey: string;
    roleLabel: string;
    roleGovernance: RoleGovernance;
    scopeType: string;
    scopeTypeLabel: string;
    scopeId: string | null;
    scopeLabel: string;
    status: string;
    user: {
        id: number | null;
        name: string | null;
        email: string | null;
        role: string | null;
    };
    updatedAt: string | null;
};

type RoleGovernance = {
    accountRole: string;
    accountRoleLabel: string;
    approvalLevel: string;
    approvalLabel: string;
    risk: string;
    riskLabel: string;
    policy: string;
};

type AssignmentTemplate = {
    key: string;
    title: string;
    description: string;
    roleKey: string;
    roleLabel: string;
    scopeType: string;
    scopeTypeLabel: string;
    scopeCode: string | null;
    scopeId: string | null;
    scopeLabel: string;
    available: boolean;
};

type Props = {
    accessScopes: AccessScope[];
    stats: {
        total: number;
        active: number;
        users: number;
        global: number;
    };
    userOptions: UserOption[];
    accountRoleOptions: {
        key: string;
        label: string;
    }[];
    roleOptions: RoleOption[];
    scopeOptions: Record<string, ScopeOption[]>;
    assignmentTemplates: AssignmentTemplate[];
};

type SharedProps = {
    flash?: {
        success?: string;
    };
    auth: {
        user: {
            role?: string;
        };
    };
};

const scopeTypeLabels: Record<string, string> = {
    global: 'کل سیستم',
    region: 'منطقه یا استان',
    venue: 'مکان پروژه',
    project: 'پروژه',
    hub: 'هاب یا رواق',
    partner: 'فروشگاه یا شریک',
    campaign: 'کمپین',
    display_network: 'شبکه نمایشگرها',
    team: 'تیم یا خانواده',
};

const userRoleLabels: Record<string, string> = {
    admin: 'ادمین',
    operator: 'اپراتور',
    viewer: 'مشاهده‌گر',
    visitor: 'بازدیدکننده',
    shop_partner: 'مدیر فروشگاه',
    hub_manager: 'مدیر هاب',
    sponsor: 'اسپانسر',
};

const accountRoleCompatibility: Record<string, string[]> = {
    admin: ['admin'],
    operator: ['admin', 'operator'],
    viewer: ['viewer'],
    visitor: ['visitor'],
    shop_partner: ['shop_partner'],
    hub_manager: ['hub_manager'],
    sponsor: ['sponsor'],
};

function compatibleUsersForRole(
    users: UserOption[],
    role?: RoleOption,
): UserOption[] {
    if (!role) {
        return [];
    }

    const allowedRoles =
        accountRoleCompatibility[role.governance.accountRole] ?? [];

    return users.filter(
        (user) =>
            user.role !== null &&
            allowedRoles.includes(user.role) &&
            !user.isStressDemo,
    );
}

function userOptionLabel(user: UserOption) {
    return `${user.name} - ${user.email} (${user.kindLabel})`;
}

function canMutate(role?: string) {
    return role === 'admin' || role === 'operator';
}

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
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
                <span className="text-sm">{label}</span>
            </div>
            <p className="mt-2 text-xl font-semibold">
                {value.toLocaleString('fa-IR')}
            </p>
        </div>
    );
}

function riskClass(risk: string) {
    return {
        critical:
            'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
        high: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
        medium:
            'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-200',
        low: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    }[risk] ?? 'bg-muted text-muted-foreground';
}

function GovernancePill({ governance }: { governance: RoleGovernance }) {
    return (
        <div className="flex flex-wrap gap-2 text-xs">
            <span className="rounded-full bg-muted px-2.5 py-1 font-medium">
                اکانت: {governance.accountRoleLabel}
            </span>
            <span className="rounded-full bg-sky-100 px-2.5 py-1 font-medium text-sky-800 dark:bg-sky-950 dark:text-sky-200">
                {governance.approvalLabel}
            </span>
            <span
                className={`rounded-full px-2.5 py-1 font-medium ${riskClass(
                    governance.risk,
                )}`}
            >
                {governance.riskLabel}
            </span>
        </div>
    );
}

export default function AccessScopesIndex({
    accessScopes,
    stats,
    userOptions,
    accountRoleOptions,
    roleOptions,
    scopeOptions,
    assignmentTemplates,
}: Props) {
    const { flash, auth } = usePage<SharedProps>().props;
    const writable = canMutate(auth.user.role);
    const activeScopes = accessScopes.filter(
        (scope) => scope.status === 'active',
    );
    const [selectedRoleKey, setSelectedRoleKey] = useState(
        roleOptions[0]?.key ?? '',
    );
    const [selectedUserId, setSelectedUserId] = useState('');
    const selectedRole = useMemo(
        () =>
            roleOptions.find((role) => role.key === selectedRoleKey) ??
            roleOptions[0],
        [roleOptions, selectedRoleKey],
    );
    const eligibleManualUsers = useMemo(
        () => compatibleUsersForRole(userOptions, selectedRole),
        [userOptions, selectedRole],
    );
    const manualUserValue = eligibleManualUsers.some(
        (user) => String(user.id) === selectedUserId,
    )
        ? selectedUserId
        : String(eligibleManualUsers[0]?.id ?? '');

    return (
        <>
            <Head title="تخصیص دسترسی کاربران" />
            <div
                dir="rtl"
                className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4"
            >
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            مدیریت عملیاتی Role + Scope
                        </p>
                        <h1 className="mt-1 text-2xl font-semibold">
                            تخصیص دسترسی کاربران
                        </h1>
                    </div>
                    <div className="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                        <Stat
                            icon={ShieldCheck}
                            label="کل دسترسی‌ها"
                            value={stats.total}
                        />
                        <Stat
                            icon={CheckCircle2}
                            label="فعال"
                            value={stats.active}
                        />
                        <Stat
                            icon={UsersRound}
                            label="کاربران"
                            value={stats.users}
                        />
                        <Stat
                            icon={Layers3}
                            label="دسترسی سراسری"
                            value={stats.global}
                        />
                    </div>
                </header>

                {flash?.success ? (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}

                <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="flex items-center gap-2">
                                <AlertTriangle className="size-4 text-muted-foreground" />
                                <h2 className="font-semibold">
                                    قاعده تغییر اکانت و نقش
                                </h2>
                            </div>
                            <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                                اکانت ورود، فقط نوع ورود کاربر را مشخص می‌کند؛
                                نقش عملیاتی و محدوده کار از همین صفحه تعیین
                                می‌شود. نقش‌های حساس مثل ادمین مرکزی، مدیر
                                منطقه‌ای، مدیر پروژه، اسپانسر بیرونی و مدیر
                                نمایشگر باید با تایید بالادست و ثبت دلیل تغییر
                                کنند.
                            </p>
                        </div>
                        <div className="grid min-w-[260px] gap-2 text-sm">
                            <div className="rounded-md bg-muted/40 p-3">
                                ادمین مرکزی: تغییر نقش‌های حساس و سراسری
                            </div>
                            <div className="rounded-md bg-muted/40 p-3">
                                مدیر پروژه: تغییر نقش‌های اجرایی همان پروژه
                            </div>
                            <div className="rounded-md bg-muted/40 p-3">
                                شرکا: فقط نقش و محدوده خودشان را دریافت می‌کنند
                            </div>
                        </div>
                    </div>
                </section>

                {writable && assignmentTemplates.length > 0 ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <div className="flex items-center gap-2">
                                    <ClipboardCheck className="size-4 text-muted-foreground" />
                                    <h2 className="font-semibold">
                                        قالب‌های آماده تخصیص دسترسی
                                    </h2>
                                </div>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    برای نقش‌های پرتکرار اکوپارک، فقط کاربر را
                                    انتخاب کنید و دسترسی محدود به همان مکان،
                                    رواق، هاب یا واحد ثبت می‌شود.
                                </p>
                            </div>
                        </div>
                        <div className="mb-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm leading-7 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                            برای سناریوهای رایج اکوپارک، از همین قالب‌های آماده استفاده کنید؛
                            نقش، دامنه و محدوده از قبل تنظیم شده‌اند و فقط باید کاربر مناسب را انتخاب کنید.
                            بخش «ثبت دسترسی جدید» برای موارد خاص، موقت یا محدوده‌هایی است که هنوز قالب آماده ندارند.
                        </div>
                        <div className="grid gap-3 xl:grid-cols-3">
                            {assignmentTemplates.map((template) => {
                                const templateRole = roleOptions.find(
                                    (role) => role.key === template.roleKey,
                                );
                                const eligibleTemplateUsers =
                                    compatibleUsersForRole(
                                        userOptions,
                                        templateRole,
                                    );
                                const hasEligibleTemplateUsers =
                                    eligibleTemplateUsers.length > 0;

                                return (
                                <Form
                                    key={template.key}
                                    action="/admin/access-scopes"
                                    method="post"
                                    options={{ preserveScroll: true }}
                                    className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border"
                                >
                                    {({ processing, errors }) => (
                                        <div className="flex h-full flex-col gap-3">
                                            <div className="min-w-0">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-medium text-cyan-800 dark:bg-cyan-950 dark:text-cyan-200">
                                                        {
                                                            template.scopeTypeLabel
                                                        }
                                                    </span>
                                                    <span
                                                        className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                                                            template.available
                                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                                                : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                                        }`}
                                                    >
                                                        {template.available
                                                            ? 'آماده ثبت'
                                                            : 'محدوده پیدا نشد'}
                                                    </span>
                                                </div>
                                                <h3 className="mt-3 font-semibold">
                                                    {template.title}
                                                </h3>
                                                <p className="mt-1 text-sm text-muted-foreground">
                                                    {template.description}
                                                </p>
                                            </div>

                                            <div className="rounded-md bg-muted/40 p-2 text-sm">
                                                <p>
                                                    <span className="text-muted-foreground">
                                                        نقش:
                                                    </span>{' '}
                                                    {template.roleLabel}
                                                </p>
                                                <p className="mt-1">
                                                    <span className="text-muted-foreground">
                                                        محدوده:
                                                    </span>{' '}
                                                    {template.scopeLabel}
                                                </p>
                                                <p
                                                    className="mt-1 text-xs text-muted-foreground"
                                                    dir="ltr"
                                                >
                                                    {template.scopeCode ??
                                                        'global'}
                                                </p>
                                            </div>

                                            {templateRole ? (
                                                    <div className="rounded-md bg-muted/25 p-2">
                                                        <GovernancePill
                                                            governance={
                                                                templateRole.governance
                                                            }
                                                        />
                                                        <p className="mt-2 text-xs leading-6 text-muted-foreground">
                                                            {
                                                                templateRole.governance
                                                                    .policy
                                                            }
                                                        </p>
                                                    </div>
                                                ) : null}

                                            <input
                                                type="hidden"
                                                name="role_key"
                                                value={template.roleKey}
                                            />
                                            <input
                                                type="hidden"
                                                name="scope_type"
                                                value={template.scopeType}
                                            />
                                            <input
                                                type="hidden"
                                                name="scope_id"
                                                value={template.scopeId ?? ''}
                                            />

                                            <div className="mt-auto grid gap-2">
                                                <Label
                                                    htmlFor={`template-user-${template.key}`}
                                                >
                                                    کاربر دریافت‌کننده دسترسی
                                                </Label>
                                                <select
                                                    id={`template-user-${template.key}`}
                                                    name="user_id"
                                                    required
                                                    defaultValue={
                                                        eligibleTemplateUsers[0]
                                                            ?.id ?? ''
                                                    }
                                                    disabled={
                                                        !hasEligibleTemplateUsers
                                                    }
                                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                >
                                                    {hasEligibleTemplateUsers ? null : (
                                                        <option value="">
                                                            کاربر مناسب برای این نقش موجود نیست
                                                        </option>
                                                    )}
                                                    {eligibleTemplateUsers.map((user) => (
                                                        <option
                                                            key={user.id}
                                                            value={user.id}
                                                        >
                                                            {userOptionLabel(
                                                                user,
                                                            )}
                                                        </option>
                                                    ))}
                                                </select>
                                                <p className="text-xs leading-6 text-muted-foreground">
                                                    فقط کاربران سازگار با نوع اکانت نقش نمایش داده می‌شوند؛ اکانت‌های دموی فشار در انتخاب عادی پنهان شده‌اند.
                                                </p>
                                                <InputError
                                                    message={errors.user_id}
                                                />
                                                <InputError
                                                    message={errors.scope_id}
                                                />
                                                <Button
                                                    type="submit"
                                                    disabled={
                                                        processing ||
                                                        !template.available ||
                                                        !hasEligibleTemplateUsers
                                                    }
                                                    className="w-full"
                                                >
                                                    ثبت این قالب
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                </Form>
                                );
                            })}
                        </div>
                    </section>
                ) : null}

                {writable ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-4 flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <div className="flex items-center gap-2">
                                    <UserCog className="size-4 text-muted-foreground" />
                                    <h2 className="font-semibold">
                                        ساخت اکانت عملیاتی یا شریک
                                    </h2>
                                </div>
                                <p className="mt-1 text-sm leading-7 text-muted-foreground">
                                    اگر چند نفر در یک سطح هستند، برای هر نفر یا شیفت یک اکانت جدا با نام روشن بسازید؛
                                    مثل «مدیر رواق اکوپارک - عملیات روز» یا «مدیر کافه اکو - شیفت عصر».
                                    بعد از ساخت، اکانت در انتخاب‌های قالب آماده و ثبت دستی ظاهر می‌شود.
                                </p>
                            </div>
                        </div>
                        <Form
                            action="/admin/access-scopes/accounts"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid gap-4 lg:grid-cols-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2 lg:col-span-1">
                                        <Label htmlFor="account-name">
                                            نام اکانت
                                        </Label>
                                        <input
                                            id="account-name"
                                            name="name"
                                            type="text"
                                            required
                                            placeholder="مثلا مدیر کافه اکو - شیفت عصر"
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2 lg:col-span-1">
                                        <Label htmlFor="account-email">
                                            ایمیل ورود
                                        </Label>
                                        <input
                                            id="account-email"
                                            name="email"
                                            type="email"
                                            required
                                            placeholder="name@example.test"
                                            dir="ltr"
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        />
                                        <InputError message={errors.email} />
                                    </div>
                                    <div className="grid gap-2 lg:col-span-1">
                                        <Label htmlFor="account-role">
                                            نوع اکانت
                                        </Label>
                                        <select
                                            id="account-role"
                                            name="role"
                                            required
                                            defaultValue="hub_manager"
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {accountRoleOptions.map((role) => (
                                                <option
                                                    key={role.key}
                                                    value={role.key}
                                                >
                                                    {role.label}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.role} />
                                    </div>
                                    <div className="flex flex-col justify-end gap-2 lg:col-span-1">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            ساخت اکانت
                                        </Button>
                                        <p className="text-xs leading-6 text-muted-foreground">
                                            رمز اولیه نسخه دمو: password
                                        </p>
                                    </div>
                                </>
                            )}
                        </Form>
                    </section>
                ) : null}

                {writable ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-4 flex items-center gap-2">
                            <Plus className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">ثبت دسترسی جدید</h2>
                        </div>
                        <p className="mb-4 text-sm leading-7 text-muted-foreground">
                            این بخش برای دسترسی‌های خاص، موقت یا محدوده‌هایی است که هنوز در قالب‌های آماده بالا تعریف نشده‌اند.
                            برای کارهای روزمره اکوپارک، اول قالب‌های آماده را استفاده کنید.
                        </p>
                        <Form
                            action="/admin/access-scopes"
                            method="post"
                            options={{ preserveScroll: true }}
                            className="grid gap-4 lg:grid-cols-5"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2 lg:col-span-2">
                                        <Label htmlFor="user_id">کاربر</Label>
                                        <select
                                            id="user_id"
                                            name="user_id"
                                            required
                                            value={manualUserValue}
                                            onChange={(event) =>
                                                setSelectedUserId(
                                                    event.target.value,
                                                )
                                            }
                                            disabled={
                                                eligibleManualUsers.length === 0
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {eligibleManualUsers.length ===
                                            0 ? (
                                                <option value="">
                                                    کاربر مناسب برای این نقش موجود نیست
                                                </option>
                                            ) : null}
                                            {eligibleManualUsers.map((user) => (
                                                <option
                                                    key={user.id}
                                                    value={user.id}
                                                >
                                                    {userOptionLabel(user)}
                                                </option>
                                            ))}
                                        </select>
                                        <p className="text-xs leading-6 text-muted-foreground">
                                            اکانت‌های دموی فشار برای تخصیص عادی نمایش داده نمی‌شوند.
                                        </p>
                                        <InputError message={errors.user_id} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="role_key">
                                            نقش عملیاتی
                                        </Label>
                                        <select
                                            id="role_key"
                                            name="role_key"
                                            required
                                            value={selectedRoleKey}
                                            onChange={(event) => {
                                                setSelectedRoleKey(
                                                    event.target.value,
                                                );
                                                setSelectedUserId('');
                                            }}
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {roleOptions.map((role) => (
                                                <option
                                                    key={role.key}
                                                    value={role.key}
                                                >
                                                    {role.label}
                                                </option>
                                            ))}
                                        </select>
                                        {selectedRole ? (
                                            <div className="rounded-md bg-muted/25 p-2">
                                                <GovernancePill
                                                    governance={
                                                        selectedRole.governance
                                                    }
                                                />
                                            </div>
                                        ) : null}
                                        <InputError message={errors.role_key} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="scope_type">
                                            دامنه
                                        </Label>
                                        <select
                                            id="scope_type"
                                            name="scope_type"
                                            required
                                            defaultValue="hub"
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {Object.keys(scopeOptions).map(
                                                (scopeType) => (
                                                    <option
                                                        key={scopeType}
                                                        value={scopeType}
                                                    >
                                                        {scopeTypeLabels[
                                                            scopeType
                                                        ] ?? scopeType}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        <InputError
                                            message={errors.scope_type}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="scope_id">
                                            شناسه محدوده
                                        </Label>
                                        <select
                                            id="scope_id"
                                            name="scope_id"
                                            defaultValue=""
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            <option value="">
                                                کل سیستم یا انتخاب دستی
                                            </option>
                                            {Object.entries(
                                                scopeOptions,
                                            ).flatMap(([scopeType, options]) =>
                                                options
                                                    .filter(
                                                        (option) => option.id,
                                                    )
                                                    .map((option) => (
                                                        <option
                                                            key={`${scopeType}-${option.id}`}
                                                            value={
                                                                option.id ?? ''
                                                            }
                                                        >
                                                            {scopeTypeLabels[
                                                                scopeType
                                                            ] ?? scopeType}
                                                            : {option.label}
                                                        </option>
                                                    )),
                                            )}
                                        </select>
                                        <InputError message={errors.scope_id} />
                                    </div>

                                    <div className="flex items-end lg:col-span-5">
                                        <Button
                                            type="submit"
                                            disabled={
                                                processing ||
                                                eligibleManualUsers.length === 0
                                            }
                                        >
                                            ثبت دسترسی
                                        </Button>
                                    </div>

                                    <div className="lg:col-span-5">
                                        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                            {roleOptions
                                                .filter((role) =>
                                                    [
                                                        'super_admin',
                                                        'project_admin',
                                                        'field_operator',
                                                        'display_ads_manager',
                                                        'shop_manager',
                                                        'external_sponsor',
                                                    ].includes(role.key),
                                                )
                                                .map((role) => (
                                                    <div
                                                        key={role.key}
                                                        className="rounded-md border border-sidebar-border/70 p-3 dark:border-sidebar-border"
                                                    >
                                                        <p className="font-medium">
                                                            {role.label}
                                                        </p>
                                                        <div className="mt-2">
                                                            <GovernancePill
                                                                governance={
                                                                    role.governance
                                                                }
                                                            />
                                                        </div>
                                                        <p className="mt-2 text-xs leading-6 text-muted-foreground">
                                                            {
                                                                role.governance
                                                                    .policy
                                                            }
                                                        </p>
                                                    </div>
                                                ))}
                                        </div>
                                    </div>
                                </>
                            )}
                        </Form>
                    </section>
                ) : null}

                <section className="rounded-lg border border-sidebar-border/70 bg-background dark:border-sidebar-border">
                    <div className="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <div>
                            <h2 className="font-semibold">
                                دسترسی‌های فعال و تاریخی
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {activeScopes.length.toLocaleString('fa-IR')}{' '}
                                دسترسی فعال
                            </p>
                        </div>
                        <UserCog className="size-5 text-muted-foreground" />
                    </div>

                    <div className="min-w-[980px]">
                        <div className="grid grid-cols-[1.2fr_1.1fr_1.1fr_0.8fr_1.2fr_0.7fr_0.8fr] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                            <span>کاربر</span>
                            <span>نقش عملیاتی</span>
                            <span>حکمرانی تغییر</span>
                            <span>دامنه</span>
                            <span>محدوده</span>
                            <span>وضعیت</span>
                            <span>عملیات</span>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {accessScopes.map((scope) => (
                                <div
                                    key={scope.id}
                                    className="grid grid-cols-[1.2fr_1.1fr_1.1fr_0.8fr_1.2fr_0.7fr_0.8fr] items-center gap-3 px-4 py-3 text-sm"
                                >
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {scope.user.name ?? '-'}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {scope.user.email ?? '-'}
                                        </p>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {scope.user.role
                                                ? (userRoleLabels[
                                                      scope.user.role
                                                  ] ?? scope.user.role)
                                                : '-'}
                                        </p>
                                    </div>
                                    <span>{scope.roleLabel}</span>
                                    <div className="grid gap-2">
                                        <GovernancePill
                                            governance={scope.roleGovernance}
                                        />
                                        <p className="text-xs leading-6 text-muted-foreground">
                                            {scope.roleGovernance.policy}
                                        </p>
                                    </div>
                                    <span>{scope.scopeTypeLabel}</span>
                                    <div className="min-w-0">
                                        <p className="truncate">
                                            {scope.scopeLabel}
                                        </p>
                                        <p
                                            className="mt-1 truncate text-xs text-muted-foreground"
                                            dir="ltr"
                                        >
                                            {scope.scopeId ?? 'global'}
                                        </p>
                                    </div>
                                    <span
                                        className={`inline-flex w-fit items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ${
                                            scope.status === 'active'
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                                : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200'
                                        }`}
                                    >
                                        {scope.status === 'active' ? (
                                            <CheckCircle2 className="size-3" />
                                        ) : (
                                            <XCircle className="size-3" />
                                        )}
                                        {scope.status === 'active'
                                            ? 'فعال'
                                            : 'غیرفعال'}
                                    </span>
                                    <div>
                                        {writable &&
                                        scope.status === 'active' ? (
                                            <Form
                                                action={`/admin/access-scopes/${scope.id}/deactivate`}
                                                method="post"
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        variant="outline"
                                                        size="sm"
                                                        disabled={processing}
                                                    >
                                                        غیرفعال
                                                    </Button>
                                                )}
                                            </Form>
                                        ) : (
                                            <span className="text-xs text-muted-foreground">
                                                {formatDate(scope.updatedAt)}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

AccessScopesIndex.layout = {
    breadcrumbs: [
        {
            title: 'تخصیص دسترسی',
            href: '/admin/access-scopes',
        },
    ],
};
