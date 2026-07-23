<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Http\Requests\Games\CreateGamePartyRequest;
use App\Http\Requests\Games\DiscoverGameHotspotRequest;
use App\Http\Requests\Games\IssueGamePassRequest;
use App\Http\Requests\Games\JoinGamePartyRequest;
use App\Http\Requests\Games\RewardedGameAdRequest;
use App\Http\Requests\Games\SelectGameRouteRequest;
use App\Http\Requests\Games\SubmitGameClueRequest;
use App\Models\Campaign;
use App\Models\GameParty;
use App\Models\Visit;
use App\Services\EcoParkOnlineGameService;
use Illuminate\Http\RedirectResponse;

class EcoParkOnlineGameActionController extends Controller
{
    public function __construct(private readonly EcoParkOnlineGameService $game) {}

    public function create(CreateGamePartyRequest $request): RedirectResponse
    {
        $campaign = $this->game->campaign();
        abort_unless($campaign instanceof Campaign, 404);
        $visit = Visit::query()->whereKey($request->validated('visit_id'))->firstOrFail();
        $this->game->createParty(
            $request->user(),
            $campaign,
            $visit,
            $request->validated(),
        );

        return back()->with('success', 'گروه بازی ساخته شد؛ حالا مسیر مشترک را انتخاب کنید.');
    }

    public function join(JoinGamePartyRequest $request): RedirectResponse
    {
        $campaign = $this->game->campaign();
        abort_unless($campaign instanceof Campaign, 404);
        $this->game->joinParty($request->user(), $campaign, $request->validated('invite_code'));

        return back()->with('success', 'به تیم پیوستید و پیشرفت مشترک برای شما فعال شد.');
    }

    public function selectRoute(SelectGameRouteRequest $request, GameParty $party): RedirectResponse
    {
        $this->game->selectRoute($request->user(), $party, $request->validated('route_key'));

        return back()->with('success', 'مسیر ثبت شد؛ سه نقطه نقشه را پیدا کنید.');
    }

    public function discoverHotspot(DiscoverGameHotspotRequest $request, GameParty $party): RedirectResponse
    {
        $this->game->discoverHotspot(
            $request->user(),
            $party,
            $request->validated('hotspot_key'),
            $request->validated('member_id'),
        );

        return back()->with('success', 'نشانه با موفقیت ثبت شد.');
    }

    public function submitClue(SubmitGameClueRequest $request, GameParty $party): RedirectResponse
    {
        $this->game->submitClue($request->user(), $party, $request->validated('answer_key'));

        return back()->with('success', 'پاسخ درست بود؛ مجوز حضور شما آماده ساخت است.');
    }

    public function issuePass(IssueGamePassRequest $request, GameParty $party): RedirectResponse
    {
        $this->game->issuePass($request->user(), $party);

        return back()->with('success', 'مجوز حضور یک‌بارمصرف ساخته شد.');
    }

    public function startSponsorBonus(RewardedGameAdRequest $request, GameParty $party): RedirectResponse
    {
        $this->game->startSponsorBonus($request->user(), $party, $request->validated('ad_request_id'));

        return back()->with('success', 'نمایش اختیاری آغاز شد؛ پس از پایان، امتیاز را دریافت کنید.');
    }

    public function completeSponsorBonus(RewardedGameAdRequest $request, GameParty $party): RedirectResponse
    {
        $this->game->completeSponsorBonus($request->user(), $party, $request->validated('ad_request_id'));

        return back()->with('success', '۳۰ امتیاز اختیاری به امتیاز مشترک اضافه شد.');
    }
}
