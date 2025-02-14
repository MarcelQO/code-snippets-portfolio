<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use DateTimeImmutable;
use Illuminate\Database\ConnectionInterface;

final class VenueDataChurnRiskQueryObject
{
    public function __construct(private readonly ConnectionInterface $connection)
    {
    }

    /** @return List<VenueChurnRiskData> */
    public function query(): array
    {
        $data = $this->connection->select(<<<SQL
            SELECT
                vac.restaurant_id,
                r.name AS restaurant_name,
                r.phone AS restaurant_phone,
                r.stripeID AS restaurant_stripe_id,
                c.stripeAccountID AS country_stripe_id,
                vac.churn_risk_status,
                vac.latest_order_timestamp
            FROM
                venue_activity_status AS vac
            INNER JOIN
                Restaurants AS r 
                    ON r.id = vac.restaurant_id
            INNER JOIN
                Countries AS c
                    ON c.id = r.countryID
            WHERE
                r.deleted IS NULL
                AND r.stripeID IS NOT NULL
                AND vac.latest_order_timestamp IS NOT NULL
            ORDER BY
                latest_order_timestamp DESC
        SQL);

        return array_map(
            static fn (object $data) => new VenueChurnRiskData(
                $data->restaurant_id,
                $data->restaurant_name,
                $data->restaurant_phone,
                $data->restaurant_stripe_id,
                $data->country_stripe_id,
                ChurnRiskStatus::from($data->churn_risk_status),
                new DateTimeImmutable($data->latest_order_timestamp),
            ),
            $data,
        );
    }
}
