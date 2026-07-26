import { Form } from '@inertiajs/react';
import { CheckCircle2, CircleAlert, Store } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Partner } from '@/types/partner-dashboard';

export function PartnerProfilePanel({
    partner,
    profileReady,
}: {
    partner: Partner;
    profileReady: boolean;
}) {
    return (
        <section className="rounded-xl border border-sidebar-border/70 bg-background">
            <div className="flex flex-col gap-3 border-b border-sidebar-border/70 p-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div className="flex items-center gap-2">
                        <Store className="size-5 text-primary" />
                        <h2 className="text-lg font-semibold">
                            اطلاعات فروشگاه
                        </h2>
                    </div>
                    <p className="mt-2 max-w-2xl text-sm leading-7 text-muted-foreground">
                        این اطلاعات به ادمین و کاربران کمک می‌کند محل و شیوه
                        تحویل پاداش را درست بشناسند. شماره مسئول فقط در دسترس
                        نقش‌های مجاز مدیریتی است.
                    </p>
                </div>
                <span
                    className={`flex w-fit items-center gap-1.5 rounded-full px-3 py-1 text-xs ${
                        profileReady
                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                            : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                    }`}
                >
                    {profileReady ? (
                        <CheckCircle2 className="size-3.5" />
                    ) : (
                        <CircleAlert className="size-3.5" />
                    )}
                    {profileReady ? 'اطلاعات کامل است' : 'نیازمند تکمیل'}
                </span>
            </div>

            <Form
                action="/partner/profile"
                method="patch"
                options={{ preserveScroll: true }}
                className="grid gap-4 p-4 md:grid-cols-2"
            >
                {({ processing, errors }) => (
                    <>
                        {Object.keys(errors).length > 0 ? (
                            <div
                                role="alert"
                                className="rounded-lg border border-destructive/25 bg-destructive/5 p-3 text-sm text-destructive md:col-span-2"
                            >
                                بعضی اطلاعات معتبر نیستند. موارد مشخص‌شده را
                                اصلاح کنید.
                            </div>
                        ) : null}
                        <div className="grid gap-2">
                            <Label htmlFor="partner_name">نام فروشگاه</Label>
                            <Input
                                id="partner_name"
                                name="name"
                                required
                                defaultValue={partner.name}
                            />
                            <InputError message={errors.name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="partner_category">
                                دسته‌بندی فعالیت
                            </Label>
                            <Input
                                id="partner_category"
                                name="category"
                                defaultValue={partner.category ?? ''}
                                placeholder="مثلاً کافه، پوشاک یا خدمات"
                            />
                            <InputError message={errors.category} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="contact_name">
                                نام مسئول تحویل
                            </Label>
                            <Input
                                id="contact_name"
                                name="contact_name"
                                defaultValue={partner.contactName ?? ''}
                                placeholder="نام و نام خانوادگی"
                            />
                            <InputError message={errors.contact_name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="contact_mobile">
                                موبایل مسئول تحویل
                            </Label>
                            <Input
                                id="contact_mobile"
                                name="contact_mobile"
                                dir="ltr"
                                inputMode="tel"
                                defaultValue={partner.contactMobile ?? ''}
                                placeholder="09xxxxxxxxx"
                            />
                            <InputError message={errors.contact_mobile} />
                        </div>
                        <div className="grid gap-2 md:col-span-2">
                            <Label htmlFor="operating_notes">
                                راهنمای عملیاتی تحویل
                            </Label>
                            <textarea
                                id="operating_notes"
                                name="operating_notes"
                                defaultValue={partner.operatingNotes ?? ''}
                                className="min-h-24 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                placeholder="ساعت پاسخگویی، محل مراجعه، مسئول شیفت و محدودیت‌های اجرایی"
                            />
                            <p className="text-xs text-muted-foreground">
                                این متن باید به همکار فروشگاه بگوید پاداش در کجا
                                و چگونه تحویل می‌شود.
                            </p>
                            <InputError message={errors.operating_notes} />
                        </div>
                        <div className="rounded-lg border bg-muted/25 p-3 md:col-span-2">
                            <div className="flex items-start gap-3">
                                <input
                                    type="hidden"
                                    name="display_visibility"
                                    value="0"
                                />
                                <input
                                    id="display_visibility"
                                    name="display_visibility"
                                    type="checkbox"
                                    value="1"
                                    defaultChecked={partner.displayVisibility}
                                    className="mt-1 size-4 rounded border border-input"
                                />
                                <div>
                                    <Label htmlFor="display_visibility">
                                        نمایش فروشگاه در تجربه‌های کاربر
                                    </Label>
                                    <p className="mt-1 text-xs leading-6 text-muted-foreground">
                                        با فعال‌بودن این گزینه، فروشگاه در
                                        مسیرهای مجاز کمپین و نمایشگرهای مربوط
                                        قابل معرفی است.
                                    </p>
                                </div>
                            </div>
                            <InputError message={errors.display_visibility} />
                        </div>
                        <div className="flex justify-end md:col-span-2">
                            <Button disabled={processing}>
                                <Store className="size-4" />
                                {processing
                                    ? 'در حال ذخیره...'
                                    : 'ذخیره اطلاعات فروشگاه'}
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </section>
    );
}
