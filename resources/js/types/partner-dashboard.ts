export type Partner = {
    id: string;
    code: string;
    name: string;
    partnerType: string;
    venueName: string | null;
    contactName: string | null;
    contactMobile: string | null;
    category: string | null;
    operatingNotes: string | null;
    displayVisibility: boolean;
};

export type RewardDefinition = {
    id: string;
    code: string;
    name: string;
    rewardType: string;
    status: string;
    pointCost: number | null;
    stockQuantity: number | null;
    userRewardsCount: number;
    awardedCount: number;
    inventoryAllocated: number;
    inventoryReserved: number;
    inventoryRedeemed: number;
    inventoryRemaining: number;
    campaignName: string | null;
    approvalStatus: string;
    availabilityStatus: string;
    rewardTier: string | null;
    rewardOption: string | null;
    availableFrom: string | null;
    availableUntil: string | null;
    description: string | null;
    terms: string | null;
    reviewNotes: string | null;
    cycleStepIndex: number | null;
    cycleStepLabel: string | null;
};

export type MissionPlanStep = {
    index: number;
    userStep: string;
    title: string;
    rewardTier: string;
    routeIntent: string;
};

export type RewardDesignTier = {
    tierKey: string;
    level: string;
    suggestedOptionCount: number;
    options: string[];
};

export type Redemption = {
    id: string;
    redemptionCode: string;
    status: string;
    redeemedAt: string | null;
    createdAt: string | null;
    visitorName: string | null;
    rewardName: string | null;
    rewardCode: string | null;
    rewardType: string | null;
    campaignName: string | null;
    campaignCode: string | null;
    conversionType: 'reward_only' | 'verified_purchase';
    purchaseConfirmed: boolean;
    purchaseAmountIrr: number | null;
    receiptReference: string | null;
};

export type PartnerAdRequest = {
    id: string;
    code: string;
    title: string;
    status: string;
    adType: string;
    creativeType: string | null;
    placementType: string | null;
    placementStatus: string | null;
    displayDeviceName: string | null;
    displayDeviceCode: string | null;
    hubName: string | null;
    startsAt: string | null;
    endsAt: string | null;
    impressionsCount: number;
    clicksCount: number;
};

export type PartnerDashboardStats = {
    rewardDefinitions: number;
    issuedRewards: number;
    pendingRedemptions: number;
    confirmedRedemptions: number;
    attributedVisits: number;
    confirmedPurchases: number;
    attributedSalesIrr: number;
    allocatedInventory: number;
    reservedInventory: number;
    redeemedInventory: number;
    remainingInventory: number;
    adRequests: number;
    pendingAds: number;
    scheduledAds: number;
};

export type PartnerDashboardProps = {
    partner: Partner;
    stats: PartnerDashboardStats;
    rewardDefinitions: RewardDefinition[];
    redemptions: Redemption[];
    adRequests: PartnerAdRequest[];
    proposalContext: {
        campaign: {
            id: string;
            code: string;
            name: string;
            status: string;
        } | null;
        missionPlan: MissionPlanStep[];
        rewardTiers: RewardDesignTier[];
    };
};

export type PartnerDashboardSection =
    | 'overview'
    | 'profile'
    | 'offers'
    | 'rewards'
    | 'redemptions';
