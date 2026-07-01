import { Link } from '@inertiajs/react';
import { ArrowRight, BookOpenCheck, ClipboardList, QrCode, Route, Trophy, UsersRound } from 'lucide-react';
import { Button } from '@/components/ui/button';

type CampaignContext = {
    code: string;
    name?: string | null;
    blueprintCode?: string | null;
};

type CampaignContextNavProps = {
    campaign: CampaignContext;
    className?: string;
};

function campaignHref(path: string, campaign: CampaignContext, blueprintAction?: string) {
    const params = new URLSearchParams({
        campaign: campaign.code,
    });

    if (campaign.blueprintCode) {
        params.set('blueprint', campaign.blueprintCode);
    }

    if (blueprintAction) {
        params.set('blueprint_action', blueprintAction);
    }

    return `${path}?${params.toString()}`;
}

export function CampaignContextNav({ campaign, className = '' }: CampaignContextNavProps) {
    const campaignName = campaign.name ?? campaign.code;

    return (
        <section className={`rounded-lg border border-border/80 bg-card/80 p-3 text-sm shadow-sm ${className}`}>
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div className="min-w-0">
                    <p className="text-xs text-muted-foreground">ادامه کار همین کمپین</p>
                    <div className="mt-1 flex flex-wrap items-center gap-2">
                        <span className="font-medium">{campaignName}</span>
                        <span className="rounded-full bg-muted px-2.5 py-1 text-[11px]" dir="ltr">
                            {campaign.code}
                        </span>
                        {campaign.blueprintCode ? (
                            <span className="rounded-full bg-primary/10 px-2.5 py-1 text-[11px] text-primary" dir="ltr">
                                {campaign.blueprintCode}
                            </span>
                        ) : null}
                    </div>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Button asChild size="sm">
                        <Link href={campaignHref('/admin/campaign-builder', campaign)}>
                            <ArrowRight className="size-4" />
                            کارگاه همین کمپین
                        </Link>
                    </Button>
                    <Button asChild variant="outline" size="sm">
                        <Link href={campaignHref('/admin/campaigns', campaign)}>
                            <ClipboardList className="size-4" />
                            کمپین‌ها
                        </Link>
                    </Button>
                    <Button asChild variant="outline" size="sm">
                        <Link href={campaignHref('/admin/qr-codes', campaign)}>
                            <QrCode className="size-4" />
                            QR
                        </Link>
                    </Button>
                    <Button asChild variant="outline" size="sm">
                        <Link href={campaignHref('/admin/missions', campaign, 'components')}>
                            <Trophy className="size-4" />
                            ماموریت و پاداش
                        </Link>
                    </Button>
                    <Button asChild variant="outline" size="sm">
                        <Link href={campaignHref('/admin/campaign-participants', campaign, 'participants')}>
                            <UsersRound className="size-4" />
                            اعضا و مشارکت‌کنندگان
                        </Link>
                    </Button>
                    <Button asChild variant="outline" size="sm">
                        <Link href={campaignHref('/admin/campaign-operations', campaign, 'route')}>
                            <Route className="size-4" />
                            نقشه عملیات
                        </Link>
                    </Button>
                    <Button asChild variant="ghost" size="sm">
                        <Link href="/admin/mission-blueprints">
                            <BookOpenCheck className="size-4" />
                            گنجینه
                        </Link>
                    </Button>
                </div>
            </div>
        </section>
    );
}
