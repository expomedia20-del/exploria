import { Head, Link } from '@inertiajs/react';
import {
    CheckCircle2,
    ClipboardList,
    ShieldCheck,
    UserCog,
    UsersRound,
} from 'lucide-react';

const sections = [
    {
        title: 'چه کسی این صفحه را می‌بیند؟',
        icon: ShieldCheck,
        items: [
            'این راهنما برای ادمین مرکزی، مدیر پروژه/اپراتور و مشاهده‌گر مدیریتی است.',
            'کاربر عادی یا مشارکت‌کننده نباید راهنمای مدیریتی را ببیند؛ مسیر او از پنل مشارکت‌کننده و انتخاب کمپین است.',
            'مدیران واحد، رواق، مکان، اسپانسر و فروشگاه در صورت نیاز از پنل اختصاصی خودشان کار می‌کنند، نه از مدیریت کل کاربران.',
        ],
    },
    {
        title: 'ثبت و تبدیل کاربران عمومی',
        icon: UsersRound,
        items: [
            'کاربر عمومی با موبایل و رضایت‌نامه وارد می‌شود و ابتدا در وضعیت کاربر عادی قرار می‌گیرد.',
            'اگر کاربر گزینه مشارکت را انتخاب کند، بدون تایید ادمین به مشارکت‌کننده تبدیل می‌شود.',
            'مشارکت‌کننده می‌تواند بعدا نوع مشارکت خود را فردی، خانوادگی یا تیمی انتخاب کند.',
        ],
    },
    {
        title: 'نقش پایه و دسترسی عملیاتی',
        icon: UserCog,
        items: [
            'نقش پایه فقط نوع اکانت را مشخص می‌کند: تیم داخلی، مدیر مکان/هاب، فروشگاه، اسپانسر، مشاهده‌گر یا بازدیدکننده.',
            'برای نقش‌های مدیریتی، محدوده واقعی کار از صفحه تخصیص دسترسی مشخص می‌شود.',
            'کاربر عمومی معمولا دسترسی عملیاتی ندارد؛ نبود دسترسی عملیاتی برای او ایراد نیست.',
        ],
    },
    {
        title: 'حذف، غیرفعال‌سازی و کنترل ریسک',
        icon: ClipboardList,
        items: [
            'اکانتی که سابقه بازدید، رضایت‌نامه، پاداش، مصرف پاداش یا اتصال فروشگاه دارد حذف مستقیم نمی‌شود.',
            'برای کاربران دارای سابقه، اول دسترسی‌های عملیاتی غیرفعال می‌شود و سوابق برای گزارش و پیگیری باقی می‌ماند.',
            'حذف امن فقط برای اکانت‌های بی‌سابقه و کم‌ریسک مجاز است.',
        ],
    },
];

export default function UserManagementGuide() {
    return (
        <>
            <Head title="راهنمای مدیریت کاربران" />

            <main className="space-y-4 p-4" dir="rtl">
                <section className="rounded-lg border border-sidebar-border/70 bg-gradient-to-l from-cyan-50 to-background p-4 dark:border-sidebar-border dark:from-cyan-950/30">
                    <p className="text-sm text-muted-foreground">
                        راهنمای داخلی ادمین‌ها برای نقش پایه، دسترسی عملیاتی و
                        کاربران عمومی
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold">
                        دستورالعمل مدیریت کاربران اکسپلوریا
                    </h1>
                    <p className="mt-2 max-w-4xl text-sm leading-7 text-muted-foreground">
                        این صفحه کمک می‌کند ادمین بداند چه زمانی باید کاربر را
                        دستی مدیریت کند، چه زمانی کاربر خودش وارد مسیر مشارکت
                        می‌شود، و چرا همه کاربران به تخصیص دسترسی عملیاتی نیاز
                        ندارند.
                    </p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <Link
                            href="/admin/users"
                            className="rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground"
                        >
                            بازگشت به مدیریت کاربران
                        </Link>
                        <Link
                            href="/admin/access-scopes"
                            className="rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                        >
                            صفحه تخصیص دسترسی
                        </Link>
                        <Link
                            href="/admin/role-operations"
                            className="rounded-md border border-input bg-background px-3 py-2 text-sm font-medium"
                        >
                            ساختار نقش‌ها
                        </Link>
                    </div>
                </section>

                <section className="grid gap-3 lg:grid-cols-2">
                    {sections.map((section) => {
                        const Icon = section.icon;

                        return (
                            <article
                                key={section.title}
                                className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border"
                            >
                                <div className="flex items-center gap-2">
                                    <Icon className="size-5 text-primary" />
                                    <h2 className="font-semibold">
                                        {section.title}
                                    </h2>
                                </div>
                                <ul className="mt-3 space-y-2 text-sm leading-7 text-muted-foreground">
                                    {section.items.map((item) => (
                                        <li key={item} className="flex gap-2">
                                            <CheckCircle2 className="mt-1 size-4 shrink-0 text-emerald-600" />
                                            <span>{item}</span>
                                        </li>
                                    ))}
                                </ul>
                            </article>
                        );
                    })}
                </section>

                <section className="rounded-lg border border-sidebar-border/70 bg-card p-4 dark:border-sidebar-border">
                    <h2 className="font-semibold">روال پیشنهادی روزمره</h2>
                    <div className="mt-3 grid gap-3 md:grid-cols-3">
                        <div className="rounded-md bg-muted/40 p-3">
                            <p className="font-medium">۱. کاربر عمومی</p>
                            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                با موبایل وارد می‌شود، رضایت‌نامه را تایید
                                می‌کند و اگر مشارکت را انتخاب کند خودش وارد چرخه
                                کمپین می‌شود.
                            </p>
                        </div>
                        <div className="rounded-md bg-muted/40 p-3">
                            <p className="font-medium">
                                ۲. اکانت مدیریتی محدود
                            </p>
                            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                توسط ادمین یا مدیر بالادستی ساخته می‌شود و بعد
                                از صفحه تخصیص دسترسی به مکان، هاب، فروشگاه یا
                                کمپین وصل می‌شود.
                            </p>
                        </div>
                        <div className="rounded-md bg-muted/40 p-3">
                            <p className="font-medium">
                                ۳. کنترل و بستن دسترسی
                            </p>
                            <p className="mt-2 text-sm leading-7 text-muted-foreground">
                                اگر همکاری تمام شد یا نقش تغییر کرد، دسترسی
                                عملیاتی غیرفعال می‌شود؛ سوابق مالی و اجرایی برای
                                گزارش باقی می‌ماند.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
