import type { MissionPlanStep } from '@/types/partner-dashboard';

export const partnerStatusLabels: Record<string, string> = {
    active: 'فعال',
    awarded: 'صادر شده',
    pending: 'در انتظار',
    confirmed: 'تأیید شده',
    redeemed: 'مصرف شده',
    draft: 'پیش‌نویس',
    inactive: 'غیرفعال',
    paused: 'متوقف',
    pending_review: 'در انتظار تأیید',
    approved: 'تأیید شده',
    rejected: 'رد شده',
    revision_requested: 'نیازمند اصلاح',
    scheduled: 'زمان‌بندی شده',
    pending_campaign_assignment: 'در انتظار اتصال به کمپین',
    pending_activation: 'در انتظار فعال‌سازی',
};

export function formatPartnerDate(value: string | null) {
    if (!value) {
        return 'ثبت نشده';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export function formatPartnerDateTimeLocal(value: string | null) {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? '' : date.toISOString().slice(0, 16);
}

export function formatPartnerCount(value: number | null | undefined) {
    return (value ?? 0).toLocaleString('fa-IR');
}

export function describeRewardOption(
    option: string,
    step: MissionPlanStep | null,
) {
    if (!option) {
        return 'در انتخاب آزاد، ادمین نوع دقیق پاداش را هنگام بررسی پیشنهاد با شما نهایی می‌کند.';
    }

    const parts: string[] = [];

    if (option.includes('ارجاع') || option.includes('همراه')) {
        parts.push('کاربر باید همراهی را دعوت یا با خود وارد مسیر کند');
    }

    if (
        option.includes('رأی') ||
        option.includes('رای') ||
        option.includes('نظر') ||
        option.includes('پاسخ')
    ) {
        parts.push('کاربر باید رأی، نظر یا پاسخ خود را در همان گام ثبت کند');
    }

    if (option.includes('کوپن') || option.includes('تخفیف')) {
        parts.push('پیشنهاد می‌تواند یک کوپن یا مزیت خرید قابل مصرف باشد');
    }

    if (option.includes('امتیاز')) {
        parts.push('پیشنهاد به امتیاز یا مزیت افزوده کاربر تبدیل می‌شود');
    }

    if (option.includes('نشان')) {
        parts.push('پیشنهاد نقش یادگاری یا مزیت نمادین دارد');
    }

    if (option.includes('گنج')) {
        parts.push('پیشنهاد پس از تکمیل شرط کشف فعال می‌شود');
    }

    if (parts.length > 0) {
        return `${parts.join('؛ ')}.`;
    }

    return step
        ? `پیشنهاد شما باید به اجرای گام «${step.userStep}» کمک کند.`
        : 'پیشنهاد باید با گزینه پاداش و شرایط اجرایی کمپین همخوان باشد.';
}
