import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Fragment, useMemo, useState } from 'react';
import {
    BookOpenCheck,
    Compass,
    Gem,
    Gift,
    Lightbulb,
    MapPinned,
    Route,
    ShieldCheck,
    Sparkles,
    Trophy,
} from 'lucide-react';
import { Button } from '@/components/ui/button';

type Principle = { title: string; body: string };
type FlowStep = { step: string; title: string; body: string };
type RewardBasketTier = { level: string; items: string[] };
type BlueprintTemplate = {
    code: string;
    title: string;
    family: string;
    bestFor: string;
    missionGoal: string;
    evidenceType: string;
    userSteps: string[];
    navigationHint: string;
    points: { base: number; bonus: string };
    rewardModel: string;
    rewardIdeas: string[];
    stakeholders: string[];
    riskControl: string;
    launchPhase: string;
    mvpPriority: number;
    priorityReason: string;
    connectedSurfaces: string[];
    rewardBasket: RewardBasketTier[];
    nextBuildAction: string;
};
type MatrixRow = { level: string; range: string; rule: string };
type RewardVaultItem = { type: string; use: string };
type GlobalPattern = { name: string; pattern: string };

type Props = {
    stats: {
        templates: number;
        missionFamilies: number;
        rewardModels: number;
        evidenceTypes: number;
        mvpFocus: number;
    };
    principles: Principle[];
    designFlow: FlowStep[];
    templates: BlueprintTemplate[];
    scoringMatrix: MatrixRow[];
    rewardVault: RewardVaultItem[];
    globalPatterns: GlobalPattern[];
};

function fa(value: number) {
    return value.toLocaleString('fa-IR');
}

function Stat({ label, value, icon: Icon }: { label: string; value: number; icon: typeof Trophy }) {
    return (
        <div className="rounded-lg border border-border/80 bg-card/80 px-3 py-2 shadow-sm">
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <p>{label}</p>
            </div>
            <p className="mt-1 font-semibold">{fa(value)}</p>
        </div>
    );
}

function Chip({ children }: { children: ReactNode }) {
    return <span className="rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground">{children}</span>;
}

export default function MissionBlueprintIndex({
    stats,
    principles,
    designFlow,
    templates,
    scoringMatrix,
    rewardVault,
    globalPatterns,
}: Props) {
    const families = useMemo(() => ['همه', ...Array.from(new Set(templates.map((template) => template.family)))], [templates]);
    const [activeFamily, setActiveFamily] = useState(families[0] ?? 'همه');
    const mvpTemplates = useMemo(
        () => [...templates].filter((template) => template.mvpPriority < 99).sort((a, b) => a.mvpPriority - b.mvpPriority),
        [templates],
    );
    const visibleTemplates = activeFamily === 'همه' ? templates : templates.filter((template) => template.family === activeFamily);
    const mvpPhases = useMemo(
        () => Array.from(new Set(mvpTemplates.map((template) => template.launchPhase))),
        [mvpTemplates],
    );

    return (
        <>
            <Head title="گنجینه مأموریت‌ها و پاداش‌ها" />
            <div dir="rtl" className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4">
                <header className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p className="text-sm text-muted-foreground">موتور طراحی بازی، گنج، امتیاز و مشوق</p>
                        <h1 className="mt-1 text-2xl font-semibold">گنجینه مأموریت‌ها و پاداش‌ها</h1>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <Link href="/games/ecopark-treasure">
                                <Sparkles className="size-4" />
                                Treasure Game
                            </Link>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href="/admin/campaign-operations">
                                <Route className="size-4" />
                                نقشه عملیات
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href="/admin/missions">
                                <Trophy className="size-4" />
                                تعریف مأموریت واقعی
                            </Link>
                        </Button>
                    </div>
                </header>

                <section className="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                    <Stat icon={Lightbulb} label="الگوی آماده" value={stats.templates} />
                    <Stat icon={BookOpenCheck} label="خانواده مأموریت" value={stats.missionFamilies} />
                    <Stat icon={Gift} label="مدل پاداش" value={stats.rewardModels} />
                    <Stat icon={ShieldCheck} label="نوع مدرک" value={stats.evidenceTypes} />
                </section>

                <section className="exploria-panel">
                    <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                        <div className="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h2 className="font-semibold">نقشه کمپین‌های اولویت‌دار</h2>
                                <p className="mt-1 text-sm text-muted-foreground">این بخش دیگر فقط چند مأموریت نیست؛ مسیر اجرای کمپین را از شروع مشترک تا شاخه‌های خانواده، هیجان، خرید، طعم‌گردی، علم، روایت شهری، ساعات خلوت و اسپانسرها نشان می‌دهد.</p>
                            </div>
                            <div className="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                {fa(mvpTemplates.length)} کمپین/مسیر قابل استفاده
                            </div>
                        </div>
                    </div>
                    <div className="space-y-5 p-4">
                        {mvpPhases.map((phase) => {
                            const phaseTemplates = mvpTemplates.filter((template) => template.launchPhase === phase);

                            return (
                                <Fragment key={phase}>
                                    <div className="flex items-center gap-3">
                                        <div className="h-px flex-1 bg-border" />
                                        <h3 className="rounded-full border border-border bg-background px-3 py-1 text-xs font-semibold text-muted-foreground">{phase}</h3>
                                        <div className="h-px flex-1 bg-border" />
                                    </div>
                                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                        {phaseTemplates.map((template) => (
                                            <article key={template.code} className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                                <div className="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p className="text-xs text-muted-foreground">اولویت {fa(template.mvpPriority)}</p>
                                                        <h4 className="mt-1 text-sm font-semibold leading-6">{template.title}</h4>
                                                    </div>
                                                    <Trophy className="size-4 text-muted-foreground" />
                                                </div>
                                                <p className="mt-2 text-xs leading-6 text-muted-foreground">{template.priorityReason}</p>
                                                <div className="mt-3 flex flex-wrap gap-2">
                                                    {template.connectedSurfaces.slice(0, 5).map((surface) => <Chip key={surface}>{surface}</Chip>)}
                                                </div>
                                                <div className="mt-3 rounded-md bg-muted/50 p-2">
                                                    <p className="text-xs font-medium">پاداش مرحله‌ای</p>
                                                    <p className="mt-1 text-xs leading-6 text-muted-foreground">
                                                        {template.rewardBasket.map((tier) => tier.level).join(' ← ')}
                                                    </p>
                                                </div>
                                            </article>
                                        ))}
                                    </div>
                                </Fragment>
                            );
                        })}
                    </div>
                </section>
                <section className="grid gap-4 xl:grid-cols-[0.85fr_1.15fr]">
                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">اصول طراحی مأموریت</h2>
                        </div>
                        <div className="grid gap-3 p-4">
                            {principles.map((principle) => (
                                <article key={principle.title} className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                    <h3 className="text-sm font-semibold">{principle.title}</h3>
                                    <p className="mt-2 text-sm leading-6 text-muted-foreground">{principle.body}</p>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">فرایند تبدیل ایده به اجرای کمپین</h2>
                        </div>
                        <div className="grid gap-3 p-4 md:grid-cols-5">
                            {designFlow.map((step) => (
                                <article key={step.step} className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                    <div className="flex items-center gap-2">
                                        <span className="flex size-7 items-center justify-center rounded-full bg-muted text-sm font-semibold">{step.step}</span>
                                        <h3 className="text-sm font-semibold">{step.title}</h3>
                                    </div>
                                    <p className="mt-2 text-xs leading-6 text-muted-foreground">{step.body}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="exploria-panel">
                    <div className="flex flex-col gap-3 border-b border-border/70 px-4 py-3 dark:border-sidebar-border lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 className="font-semibold">کتابخانه کمپین‌ها و مأموریت‌های قابل ترکیب</h2>
                            <p className="mt-1 text-sm text-muted-foreground">هر مورد می‌تواند به‌تنهایی یک کمپین کوتاه باشد یا با مسیر مادر، پاداش‌های چهارسطحی و شاخه‌های اکوپارک ترکیب شود.</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {families.map((family) => (
                                <button
                                    key={family}
                                    type="button"
                                    onClick={() => setActiveFamily(family)}
                                    className={`rounded-md border px-3 py-1.5 text-sm transition ${
                                        activeFamily === family
                                            ? 'border-primary bg-primary text-primary-foreground'
                                            : 'border-sidebar-border/70 hover:bg-muted/60 dark:border-sidebar-border'
                                    }`}
                                >
                                    {family}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="grid gap-4 p-4 lg:grid-cols-2">
                        {visibleTemplates.map((template) => (
                            <article key={template.code} className="rounded-lg border border-border/80 bg-card/75 p-4 shadow-sm">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <Sparkles className="size-4 text-muted-foreground" />
                                            <h3 className="font-semibold">{template.title}</h3>
                                        </div>
                                        <p className="mt-1 text-xs text-muted-foreground" dir="ltr">{template.code}</p>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        <Chip>{template.launchPhase}</Chip>
                                        {template.mvpPriority < 99 && <Chip>اولویت {fa(template.mvpPriority)}</Chip>}
                                        <Chip>{template.family}</Chip>
                                        <Chip>{template.rewardModel}</Chip>
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-3 text-sm md:grid-cols-2">
                                    <div>
                                        <p className="text-xs text-muted-foreground">هدف مأموریت</p>
                                        <p className="mt-1 leading-6">{template.missionGoal}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">بهترین کاربرد</p>
                                        <p className="mt-1 leading-6">{template.bestFor}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">مدرک انجام</p>
                                        <p className="mt-1 leading-6">{template.evidenceType}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">امتیاز پیشنهادی</p>
                                        <p className="mt-1 leading-6">{fa(template.points.base)} امتیاز · {template.points.bonus}</p>
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-3 md:grid-cols-2">
                                    <div className="rounded-lg bg-muted/40 p-3">
                                        <div className="mb-2 flex items-center gap-2">
                                            <Compass className="size-4 text-muted-foreground" />
                                            <h4 className="text-sm font-semibold">مراحل کاربر</h4>
                                        </div>
                                        <ol className="space-y-1 text-sm text-muted-foreground">
                                            {template.userSteps.map((step, index) => (
                                                <li key={step} className="flex gap-2">
                                                    <span className="font-semibold text-foreground">{fa(index + 1)}</span>
                                                    <span>{step}</span>
                                                </li>
                                            ))}
                                        </ol>
                                    </div>
                                    <div className="rounded-lg bg-muted/40 p-3">
                                        <div className="mb-2 flex items-center gap-2">
                                            <MapPinned className="size-4 text-muted-foreground" />
                                            <h4 className="text-sm font-semibold">ناوبری و مسیر</h4>
                                        </div>
                                        <p className="text-sm leading-6 text-muted-foreground">{template.navigationHint}</p>
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-3 md:grid-cols-3">
                                    <div>
                                        <p className="text-xs text-muted-foreground">ایده‌های پاداش</p>
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {template.rewardIdeas.map((idea) => <Chip key={idea}>{idea}</Chip>)}
                                        </div>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">ذی‌نفعان</p>
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {template.stakeholders.map((stakeholder) => <Chip key={stakeholder}>{stakeholder}</Chip>)}
                                        </div>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">کنترل ریسک</p>
                                        <p className="mt-2 text-sm leading-6 text-muted-foreground">{template.riskControl}</p>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="grid gap-4 xl:grid-cols-3">
                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">ماتریس امتیازدهی</h2>
                        </div>
                        <div className="divide-y divide-border/70">
                            {scoringMatrix.map((row) => (
                                <article key={row.level} className="px-4 py-3 text-sm">
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="font-medium">{row.level}</span>
                                        <span className="text-xs text-muted-foreground">{row.range}</span>
                                    </div>
                                    <p className="mt-1 leading-6 text-muted-foreground">{row.rule}</p>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">مخزن پاداش‌ها</h2>
                        </div>
                        <div className="grid gap-3 p-4">
                            {rewardVault.map((reward) => (
                                <article key={reward.type} className="rounded-lg border border-border/80 bg-card/75 p-3 shadow-sm">
                                    <div className="flex items-center gap-2">
                                        <Gift className="size-4 text-muted-foreground" />
                                        <h3 className="text-sm font-semibold">{reward.type}</h3>
                                    </div>
                                    <p className="mt-2 text-sm leading-6 text-muted-foreground">{reward.use}</p>
                                </article>
                            ))}
                        </div>
                    </div>

                    <div className="exploria-panel">
                        <div className="border-b border-border/70 px-4 py-3 dark:border-sidebar-border">
                            <h2 className="font-semibold">الگوگیری جهانی</h2>
                        </div>
                        <div className="divide-y divide-border/70">
                            {globalPatterns.map((pattern) => (
                                <article key={pattern.name} className="px-4 py-3 text-sm">
                                    <div className="flex items-center gap-2">
                                        <Gem className="size-4 text-muted-foreground" />
                                        <h3 className="font-medium">{pattern.name}</h3>
                                    </div>
                                    <p className="mt-1 leading-6 text-muted-foreground">{pattern.pattern}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}

MissionBlueprintIndex.layout = {
    title: 'گنجینه مأموریت‌ها و پاداش‌ها',
    breadcrumbs: [
        {
            title: 'گنجینه مأموریت‌ها و پاداش‌ها',
            href: '/admin/mission-blueprints',
        },
    ],
};
