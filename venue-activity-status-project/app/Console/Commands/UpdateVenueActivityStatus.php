<?php

declare(strict_types=1);

namespace Commands;

use Illuminate\Contracts\Bus\Dispatcher;
use Infrastructure\Venue\AclPermission;
use Infrastructure\Venue\ChurnPreventionAlgorithm;
use Infrastructure\Venue\VenueData;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Infrastructure\Venue\StripeService;
use Infrastructure\Venue\ChurnRiskStatus;
use Infrastructure\Venue\VenueDataQueryObject;
use function App\Console\Commands\app;

final class UpdateVenueActivityStatus extends Command
{
    protected $signature = 'venue:update-activity-status';
    protected $description = 'Updates venue_activity_status table';

    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly VenueDataQueryObject $venueDataQueryObject,
        private readonly StripeService $stripeService,
        private readonly Dispatcher $queueDispatcher,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->connection->table('venue_activity_status')->truncate();

        $ignoredVenues = $this->getIgnoredVenues();

        $this->connection->table('venue_activity_status')->insert(
            array_map(
                fn (VenueData $venue) => [
                    'restaurant_id' => $venue->id,
                    'latest_order_timestamp' => $venue->latestOrder,
                    'churn_risk_status' => $this->calculateChurnRisk($venue, $ignoredVenues)->value,
                ],
                $this->venueDataQueryObject->query(),
            ),
        );

        $this->queueDispatcher->dispatch(app()->make(ChurnPreventionAlgorithm::class));

        $this->info('Meta data table has been filled.');
    }

    private function calculateChurnRisk(VenueData $venue, array $ignoredVenues): ChurnRiskStatus
    {

        if(in_array($venue->id, $ignoredVenues, true))
        {
            return ChurnRiskStatus::IGNORED;
        }

        if(!$this->stripeService->venueHasActiveSubscription($venue))
        {
            return ChurnRiskStatus::LOW;
        }

        return match($venue->hasHadOrdersWithinTheLastWeek()) {
            true => match($this->stripeService->venueHasUnpaidInvoice($venue)) {
                true => ChurnRiskStatus::MEDIUM,
                false => ChurnRiskStatus::LOW,
            },
            false => match($this->stripeService->venueHasUnpaidInvoice($venue)) {
                true => ChurnRiskStatus::HIGH,
                false => ChurnRiskStatus::MEDIUM,
            },
        };
    }

    private function getIgnoredVenues(): array
    {
        return $this->connection
            ->table('acl_permission_venue AS apv')
            ->select(['r.id'])
            ->join('acl_permissions AS ap', 'ap.id', '=', 'apv.acl_permission_id')
            ->join('Restaurants AS r', 'r.uuid', '=', 'apv.venue_id')
            ->where('ap.name', '=', AclPermission::IGNORE_CHURN_RISK_CALCULATION)
            ->pluck('r.id')
            ->all();
    }
}
