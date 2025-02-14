<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Database\ConnectionInterface;

class VenueDataQueryObject
{
    public function __construct(private readonly ConnectionInterface $connection)
    {
    }

    /** @return List<VenueData> */
    public function query(): array
    {
        $data = $this->connection->select(
            <<<SQL
                SELECT
                    r.id AS restaurant_id,
                    r.stripeID AS restaurant_stripe_id,
                    c.stripeAccountID AS country_stripe_id,
                    oa.latest_order_timestamp
                FROM
                    Restaurants AS r
                INNER JOIN
                    Countries AS c
                        ON c.id = r.countryID
                LEFT JOIN
                    (
                        SELECT
                            o.restaurantID,
                            MAX(o.created) AS latest_order_timestamp
                        FROM
                            Orders AS o
                        WHERE
                            o.created <= :currentTimestamp
                        GROUP BY
                            o.restaurantID
                    ) oa ON r.id = oa.restaurantID
                WHERE
                    r.stripeID IS NOT NULL
                ORDER BY
                    latest_order_timestamp DESC
            SQL,
            [
                'currentTimestamp' => Carbon::now(),
            ],
        );

        return array_map(
            static fn (object $data) => new VenueData(
                $data->restaurant_id,
                $data->restaurant_stripe_id,
                $data->country_stripe_id,
                $data->latest_order_timestamp !== null
                    ? new DateTimeImmutable($data->latest_order_timestamp)
                    : null,
            ),
            $data,
        );
    }
}
