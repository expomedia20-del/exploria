import { Form, Head, usePage } from '@inertiajs/react';
import {
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
};

type RoleOption = {
    key: string;
    label: string;
    defaultScope: string;
};

type ScopeOption = {
    id: string | null;
    label: string;
};

type AccessScope = {
    id: string;
    roleKey: string;
    roleLabel: string;
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

export default function AccessScopesIndex({
    accessScopes,
    stats,
    userOptions,
    roleOptions,
    scopeOptions,
    assignmentTemplates,
}: Props) {
    const { flash, auth } = usePage<SharedProps>().props;
    const writable = canMutate(auth.user.role);
    const activeScopes = accessScopes.filter(
        (scope) => scope.status === 'active',
    );

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
                        <div className="grid gap-3 xl:grid-cols-3">
                            {assignmentTemplates.map((template) => (
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
                                                        userOptions[0]?.id ?? ''
                                                    }
                                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                                >
                                                    {userOptions.map((user) => (
                                                        <option
                                                            key={user.id}
                                                            value={user.id}
                                                        >
                                                            {user.name} -{' '}
                                                            {user.email}
                                                        </option>
                                                    ))}
                                                </select>
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
                                                        userOptions.length === 0
                                                    }
                                                    className="w-full"
                                                >
                                                    ثبت این قالب
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                </Form>
                            ))}
                        </div>
                    </section>
                ) : null}

                {writable ? (
                    <section className="rounded-lg border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border">
                        <div className="mb-4 flex items-center gap-2">
                            <Plus className="size-4 text-muted-foreground" />
                            <h2 className="font-semibold">ثبت دسترسی جدید</h2>
                        </div>
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
                                            defaultValue={
                                                userOptions[0]?.id ?? ''
                                            }
                                            className="h-9 rounded-md border border-input bg-background px-3 text-sm"
                                        >
                                            {userOptions.map((user) => (
                                                <option
                                                    key={user.id}
                                                    value={user.id}
                                                >
                                                    {user.name} - {user.email}
                                                </option>
                                            ))}
                                        </select>
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
                                            defaultValue={
                                                roleOptions[0]?.key ?? ''
                                            }
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
                                            disabled={processing}
                                        >
                                            ثبت دسترسی
                                        </Button>
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
                        <div className="grid grid-cols-[1.2fr_1.1fr_0.8fr_1.2fr_0.7fr_0.8fr] gap-3 border-b border-sidebar-border/70 px-4 py-3 text-xs font-medium text-muted-foreground dark:border-sidebar-border">
                            <span>کاربر</span>
                            <span>نقش عملیاتی</span>
                            <span>دامنه</span>
                            <span>محدوده</span>
                            <span>وضعیت</span>
                            <span>عملیات</span>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {accessScopes.map((scope) => (
                                <div
                                    key={scope.id}
                                    className="grid grid-cols-[1.2fr_1.1fr_0.8fr_1.2fr_0.7fr_0.8fr] items-center gap-3 px-4 py-3 text-sm"
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
