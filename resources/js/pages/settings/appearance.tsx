import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="تنظیمات نمایش" />

            <h1 className="sr-only">تنظیمات نمایش</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="تنظیمات نمایش"
                    description="حالت روشن، تاریک یا هماهنگ با سیستم را انتخاب کنید."
                />
                <AppearanceTabs />
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'تنظیمات نمایش',
            href: editAppearance(),
        },
    ],
};
