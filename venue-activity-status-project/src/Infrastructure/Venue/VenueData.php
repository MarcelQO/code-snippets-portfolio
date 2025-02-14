<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Carbon\CarbonImmutable;
use DateTimeImmutable;

final class VenueData
{
    public function __construct(
        public readonly int $id,
        public readonly string $stripeId,
        public readonly string $stripeCountryId,
        public readonly ?DateTimeImmutable $latestOrder,
    ) {
    }

    public function hasHadOrdersWithinTheLastWeek(): bool
    {
        return CarbonImmutable::now()->diffInDays($this->latestOrder) <= 7;
    }

    public function hadOrdersWithinLastMonth(): bool
    {
        if ($this->latestOrder === null) {
            return false;
        }

        return CarbonImmutable::now()->diffInDays($this->latestOrder) < 30;
    }
}
