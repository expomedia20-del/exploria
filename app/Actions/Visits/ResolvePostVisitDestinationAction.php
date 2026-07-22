<?php

namespace App\Actions\Visits;

use App\Models\Visit;

class ResolvePostVisitDestinationAction
{
    private const GAME_CAMPAIGN_CODES = [
        'ecopark-pilot-1405',
        'ecopark-online-treasure-map-game-campaign',
    ];

    private const GAME_BLUEPRINT_CODES = [
        'ecopark-online-treasure-map-game',
    ];

    public function execute(Visit $visit): string
    {
        $visit->loadMissing('campaign');

        if ($visit->campaign && $this->isGameCampaign($visit->campaign->code, $visit->campaign->campaign_type, $visit->campaign->metadata)) {
            return route('games.ecopark-treasure', ['visit' => $visit->id]);
        }

        return route('visits.show', $visit);
    }

    /** @param array<string, mixed>|null $metadata */
    private function isGameCampaign(string $code, string $campaignType, ?array $metadata): bool
    {
        if ($campaignType === 'treasure_route') {
            return true;
        }

        if (in_array($code, self::GAME_CAMPAIGN_CODES, true)) {
            return true;
        }

        return in_array((string) ($metadata['blueprint_code'] ?? ''), self::GAME_BLUEPRINT_CODES, true);
    }
}
