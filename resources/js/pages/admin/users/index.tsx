import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import {
    Ban,
    CheckCircle2,
    Filter,
    ShieldCheck,
    Trash2,
    UserCog,
    UsersRound,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';

type RoleOption = {
    key: string;
    label: string;
};

type UserScope = {
    id: string;
    roleKey: string;
    roleLabel: string;
    scopeType: string;
    scopeTypeLabel: string;
    scopeId: string | null;
    scopeLabel: string;
    status: string;
    statusLabel: string;
};

type ManagedUser = {
    id: number;
    name: string;
    email: string;
    mobile?: string | null;
    role: string | null;
    roleLabel: string;
    kind: string;
    kindLabel: string;
    statusLabel: string;
    publicStatus: string;
    publicStatusLabel: string;
    publicParticipationMode: string;
    isStressDemo: boolean;
    counts: {
        accessScopes: number;
        activeScopes: number;
        visits: number;
        missionProgress: number;
        rewards: number;
        redemptions: number;
        consents: number;
    };
    canDelete: boolean;
    deleteBlockers: string[];
    scopes: UserScope[];
};

type Props = {
    users: ManagedUser[];
    stats: {
        total: number;
        internal: number;
        partners: number;
        visitors: number;
        publicRegistered: number;
        publicParticipants: number;
        activeScopedUsers: number;
    };
    roleOptions: RoleOption[];
    filters: Array<{
        key: string;
        label: string;
    }>;
};

type SharedProps = {
    flash?: {
        success?: string;
    };
    errors?: Record<string, string>;
};

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

function statusClass(user: ManagedUser) {
    if (user.counts.activeScopes > 0) {
        return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200';
    }

    if (user.role === 'visitor' && user.publicStatus === 'participant') {
        return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-200';
    }

    if (user.role === 'visitor' && user.publicStatus === 'registered') {
        return 'bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-200';
    }

    if (user.counts.visits > 0) {
        return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-200';
    }

    return 'bg-muted text-muted-foreground';
}

function countLabel(value: number, label: string) {
    return `${value.toLocaleString('fa-IR')} ${label}`;
}

export default function AdminUsersIndex({
    users,
    stats,
    roleOptions,
    filters,
}: Props) {
    const { flash, errors } = usePage<SharedProps>().props;
    const [activeFilter, setActiveFilter] = useState('all');
    const [query, setQuery] = useState('');

    const visibleUsers = useMemo(() => {
        const normalizedQuery = query.trim().toLowerCase();

        return users.filter((user) => {
            const matchesFilter =
                activeFilter === 'all' ||
                user.kind === activeFilter ||
                (activeFilter === 'public_registered' &&
                    user.role === 'visitor' &&
                    user.publicStatus === 'registered') ||
                (activeFilter === 'public_participant' &&
                    user.role === 'visitor' &&
                    user.publicStatus === 'participant');
            const matchesQuery =
                normalizedQuery.length === 0 ||
                user.name.toLowerCase().includes(normalizedQuery) ||
                user.email.toLowerCase().includes(normalizedQuery) ||
                user.mobile?.includes(normalizedQuery) ||
                user.roleLabel.toLowerCase().includes(normalizedQuery) ||
                user.kindLabel.toLowerCase().includes(normalizedQuery) ||
                user.publicStatusLabel.toLowerCase().includes(normalizedQuery);

            return matchesFilter && matchesQuery;
        });
    }, [activeFilter, query, users]);

    return (
        <>
            <Head title="مدیریت کاربران" />

            <main className="space-y-4 p-4" dir="rtl">
                <section className="rounded-lg border border-sidebar-border/70 bg-gradient-to-l from-cyan-50 to-background p-4 dark:border-sidebar-border dark:from-cyan-950/30">
                    <p className="text-sm text-muted-foreground">
                        مدیریت اکانت‌ها، نقش پایه، دسترسی‌های عملیاتی و وضعیت
                        حذف ایمن
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold">
                        مدیریت کاربران اکسپلوریا
                    </h1>
                    <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                        نقش پایه مشخص می‌کند کاربر از چه نوع اکانتی وارد می‌شود.
                        محدوده و مسئولیت واقعی هر پروژه همچنان از صفحه تخصیص
                        دسترسی تعریف می‌شود.
                    </p>
                    <div className="mt-4">
                        <div className="flex flex-wrap gap-2">
                            <Link
                                href="/admin/role-operations"
                                className="inline-flex rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                            >
                                نقشه نقش‌ها
                            </Link>
                            <Link
                                href="/admin/access-scopes"
                                className="inline-flex rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground"
                            >
                                ساخت و تخصیص دسترسی
                            </Link>
                            <Link
                                href="/admin/users/guide"
                                className="inline-flex rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                            >
                                راهنمای نقش و کاربر
                            </Link>
                        </div>
                    </div>
                </section>

                {flash?.success && (
                    <div className="rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                        {flash.success}
                    </div>
                )}
                {errors?.delete && (
                    <div className="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">
                        {errors.delete}
                    </div>
                )}

                <section className="grid gap-3 md:grid-cols-7">
                    <Stat
                        icon={UsersRound}
                        label="کل کاربران"
                        value={stats.total}
                    />
                    <Stat
                        icon={UserCog}
                        label="تیم داخلی"
                        value={stats.internal}
                    />
                    <Stat
                        icon={ShieldCheck}
                        label="واحدها و اسپانسرها"
                        value={stats.partners}
                    />
                    <Stat
                        icon={UsersRound}
                        label="بازدیدکنندگان"
                        value={stats.visitors}
                    />
                    <Stat
                        icon={UsersRound}
                        label="کاربر عادی"
                        value={stats.publicRegistered}
                    />
                    <Stat
                        icon={CheckCircle2}
                        label="مشارکت‌کننده"
                        value={stats.publicParticipants}
                    />
                    <Stat
                        icon={CheckCircle2}
                        label="دارای دسترسی فعال"
                        value={stats.activeScopedUsers}
                    />
                </section>

                <section className="rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                    <div className="flex flex-wrap items-center gap-2">
                        <Filter className="size-4 text-muted-foreground" />
                        {filters.map((filter) => (
                            <button
                                key={filter.key}
                                type="button"
                                onClick={() => setActiveFilter(filter.key)}
                                className={`rounded-md px-3 py-1.5 text-sm transition ${
                                    activeFilter === filter.key
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-muted-foreground hover:bg-muted/70'
                                }`}
                            >
                                {filter.label}
                            </button>
                        ))}
                        <input
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="جستجو بر اساس نام، ایمیل یا نقش"
                            className="min-h-9 flex-1 rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-2 focus:ring-ring"
                        />
                    </div>
                </section>

                <section className="space-y-3">
                    {visibleUsers.map((user) => (
                        <article
                            key={user.id}
                            className="rounded-lg border border-sidebar-border/70 bg-card p-3 dark:border-sidebar-border"
                        >
                            <div className="grid gap-4 xl:grid-cols-[1.1fr_1.2fr_1fr]">
                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span
                                            className={`rounded-full px-2.5 py-1 text-xs font-medium ${statusClass(user)}`}
                                        >
                                            {user.statusLabel}
                                        </span>
                                        <span className="rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground">
                                            {user.kindLabel}
                                        </span>
                                        {user.isStressDemo && (
                                            <span className="rounded-full bg-amber-100 px-2.5 py-1 text-xs text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                                                اکانت دموی فشار
                                            </span>
                                        )}
                                    </div>
                                    <h2 className="mt-3 font-semibold">
                                        {user.name}
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {user.email}
                                    </p>
                                    {user.mobile ? (
                                        <p
                                            className="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200"
                                            dir="ltr"
                                        >
                                            {user.mobile}
                                        </p>
                                    ) : null}
                                    <p className="mt-2 text-sm">
                                        نقش پایه:{' '}
                                        <strong>{user.roleLabel}</strong>
                                    </p>
                                    {user.role === 'visitor' && (
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            مسیر عمومی: {user.publicStatusLabel}
                                        </p>
                                    )}
                                    <div className="mt-3 flex flex-wrap gap-2 text-xs text-muted-foreground">
                                        <span>
                                            {countLabel(
                                                user.counts.visits,
                                                'بازدید',
                                            )}
                                        </span>
                                        <span>
                                            {countLabel(
                                                user.counts.rewards,
                                                'پاداش',
                                            )}
                                        </span>
                                        <span>
                                            {countLabel(
                                                user.counts.redemptions,
                                                'مصرف',
                                            )}
                                        </span>
                                        <span>
                                            {countLabel(
                                                user.counts.consents,
                                                'رضایت‌نامه',
                                            )}
                                        </span>
                                    </div>
                                </div>

                                <div className="rounded-md bg-muted/30 p-3">
                                    <div className="flex items-center justify-between gap-2">
                                        <h3 className="font-medium">
                                            دسترسی‌های عملیاتی
                                        </h3>
                                        <Link
                                            href="/admin/access-scopes"
                                            className="text-sm text-primary underline-offset-4 hover:underline"
                                        >
                                            تخصیص دسترسی
                                        </Link>
                                    </div>
                                    {user.scopes.length === 0 ? (
                                        <p className="mt-3 text-sm text-muted-foreground">
                                            {user.role === 'visitor'
                                                ? 'کاربر عمومی نیازی به دسترسی عملیاتی ندارد؛ مشارکت او از پنل کاربر و انتخاب کمپین فعال می‌شود.'
                                                : 'هنوز دسترسی عملیاتی برای این کاربر ثبت نشده است.'}
                                        </p>
                                    ) : (
                                        <div className="mt-3 space-y-2">
                                            {user.scopes.map((scope) => (
                                                <div
                                                    key={scope.id}
                                                    className="rounded-md border border-border/70 bg-background p-2 text-sm"
                                                >
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <span className="font-medium">
                                                            {scope.roleLabel}
                                                        </span>
                                                        <span className="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                                                            {scope.statusLabel}
                                                        </span>
                                                    </div>
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        {scope.scopeTypeLabel}:{' '}
                                                        {scope.scopeLabel}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-3">
                                    <Form
                                        action={`/admin/users/${user.id}/role`}
                                        method="patch"
                                        options={{ preserveScroll: true }}
                                        className="rounded-md border border-border/70 p-3"
                                    >
                                        {({ processing, errors }) => (
                                            <div className="space-y-2">
                                                <label className="text-sm font-medium">
                                                    تغییر نقش پایه
                                                </label>
                                                <select
                                                    name="role"
                                                    defaultValue={
                                                        user.role ?? ''
                                                    }
                                                    className="min-h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
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
                                                <InputError
                                                    message={errors.role}
                                                />
                                                {user.role === 'visitor' && (
                                                    <p className="text-xs leading-6 text-muted-foreground">
                                                        تغییر نقش پایه برای
                                                        کاربر عمومی فقط برای
                                                        اصلاح اکانت است؛ شروع
                                                        مشارکت از پنل کاربر
                                                        انجام می‌شود.
                                                    </p>
                                                )}
                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                    className="w-full"
                                                >
                                                    ثبت نقش پایه
                                                </Button>
                                            </div>
                                        )}
                                    </Form>

                                    <Form
                                        action={`/admin/users/${user.id}/deactivate-access`}
                                        method="post"
                                        options={{ preserveScroll: true }}
                                    >
                                        {({ processing }) => (
                                            <Button
                                                type="submit"
                                                variant="outline"
                                                disabled={
                                                    processing ||
                                                    user.counts.activeScopes ===
                                                        0
                                                }
                                                className="w-full gap-2"
                                            >
                                                <Ban className="size-4" />
                                                غیرفعال‌سازی دسترسی‌ها
                                            </Button>
                                        )}
                                    </Form>

                                    <Form
                                        action={`/admin/users/${user.id}`}
                                        method="delete"
                                        options={{ preserveScroll: true }}
                                    >
                                        {({ processing, errors }) => (
                                            <div className="space-y-2">
                                                <Button
                                                    type="submit"
                                                    variant={
                                                        user.canDelete
                                                            ? 'destructive'
                                                            : 'outline'
                                                    }
                                                    disabled={
                                                        processing ||
                                                        !user.canDelete
                                                    }
                                                    className="w-full gap-2"
                                                >
                                                    <Trash2 className="size-4" />
                                                    {user.canDelete
                                                        ? 'حذف ایمن'
                                                        : 'حذف بسته است'}
                                                </Button>
                                                {!user.canDelete && (
                                                    <p className="text-xs leading-6 text-muted-foreground">
                                                        حذف بسته است، چون سابقه
                                                        دارد:{' '}
                                                        {user.deleteBlockers.join(
                                                            '، ',
                                                        )}
                                                    </p>
                                                )}
                                                <InputError
                                                    message={errors.delete}
                                                />
                                            </div>
                                        )}
                                    </Form>
                                </div>
                            </div>
                        </article>
                    ))}

                    {visibleUsers.length === 0 && (
                        <div className="rounded-lg border border-dashed border-sidebar-border/70 p-6 text-center text-sm text-muted-foreground">
                            کاربری با این فیلتر پیدا نشد.
                        </div>
                    )}
                </section>
            </main>
        </>
    );
}
