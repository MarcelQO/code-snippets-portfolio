<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Domain\Model\VenueManagement\ValueObjects\VenueId;
use Illuminate\Database\ConnectionInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final class UpdateChurnRiskWhitelistQueryObject
{
    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {
    }

    public function removeVenueFromChurnRiskWhitelist(VenueId $venueId): void
    {
        $this->connection->table('acl_permission_venue')
            ->join('acl_permissions', 'acl_permissions.id', '=', 'acl_permission_venue.acl_permission_id')
            ->where('acl_permissions.name', AclPermission::IGNORE_CHURN_RISK_CALCULATION->value)
            ->where('acl_permission_venue.venue_id', Uuid::fromString($venueId->toString())->getBytes())
            ->delete();
    }

    public function addVenueToChurnRiskWhitelist(VenueId $venueId): void
    {
        $aclPermissionId = $this->connection->table('acl_permissions')
            ->where('name', AclPermission::IGNORE_CHURN_RISK_CALCULATION->value)
            ->value('id');

        if ($aclPermissionId === null) {
            throw new RuntimeException('Acl permission not found');
        }

        $this->connection->table('acl_permission_venue')->insert([
            'acl_permission_id' => $aclPermissionId,
            'venue_id' => Uuid::fromString($venueId->toString())->getBytes(),
        ]);
    }

    public function setChurnRiskBadgeAsLow(VenueId $venueId): void
    {
        $this->updateChurnRiskBadge($venueId, ChurnRiskStatus::LOW);
    }

    public function setChurnRiskBadgeAsIgnored(VenueId $venueId): void
    {
        $this->updateChurnRiskBadge($venueId, ChurnRiskStatus::IGNORED);
    }

    private function updateChurnRiskBadge(VenueId $venueId, ChurnRiskStatus $churnRiskStatus): void
    {
        $this->connection->table('venue_activity_status')
            ->join('Restaurants', 'Restaurants.id', '=', 'venue_activity_status.restaurant_id')
            ->where('Restaurants.uuid', Uuid::fromString($venueId->toString())->getBytes())
            ->update([
                'churn_risk_status' => $churnRiskStatus->value,
            ]);
    }
}
