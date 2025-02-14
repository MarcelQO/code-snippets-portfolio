<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Domain\Model\VenueManagement\ValueObjects\VenueId;
use Illuminate\Database\ConnectionInterface;
use Ramsey\Uuid\Uuid;

final class CheckChurnRiskWhitelistQueryObject
{
    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {
    }

    public function isVenueAlreadyInChurnRiskWhitelist(VenueId $venueId): bool
    {
        return $this->connection->table('acl_permission_venue')
            ->join('acl_permissions', 'acl_permissions.id', '=', 'acl_permission_venue.acl_permission_id')
            ->where('acl_permissions.name', AclPermission::IGNORE_CHURN_RISK_CALCULATION->value)
            ->where('acl_permission_venue.venue_id', Uuid::fromString($venueId->toString())->getBytes())
            ->exists();
    }
}
