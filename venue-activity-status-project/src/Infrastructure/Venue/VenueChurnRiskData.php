<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Carbon\CarbonImmutable;
use DateTimeImmutable;

final class VenueChurnRiskData
{
    public function __construct(
        public readonly int $venueId,
        public readonly string $venueName,
        public readonly ?string $venuePhone,
        public readonly string $stripeId,
        public readonly string $stripeCountryId,
        public readonly ChurnRiskStatus $churnRiskStatus,
        public readonly DateTimeImmutable $latestOrder,
    ) {
    }

    public function daysSinceLastOrder(): int
    {
        return CarbonImmutable::now()->diffInDays($this->latestOrder);
    }
}
