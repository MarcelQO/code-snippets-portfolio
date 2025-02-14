<?php

namespace Commands;

use App\Models\Tenants\Tenant;
use Infrastructure\Venue\StripeService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Infrastructure\Venue\VenueData;
use Infrastructure\Venue\VenueDataQueryObject;
use function App\Console\Commands\collect;

class DeleteInactiveVenues extends Command {

    protected $signature = 'nova:delete-inactive-venues';

    protected $description = 'Delete inactive venues for a better overview in Nova';

    /**
     * @var VenueData[]
     */
    private array $venues;

    public function __construct(
        private readonly StripeService $stripeService,
    ) {
        parent::__construct();
    }

    public function handle(VenueDataQueryObject $venueDataQueryObject): void {
        $this->venues = $venueDataQueryObject->query();

        // A venue is considered inactive and should be soft deleted if it has no active subscription,
        // no data storage and no orders within the last month,
        // and trial did not end within the last 30 days.

        // Fetch all venues
        $allVenues = Tenant::all();

        foreach ($allVenues as $venue) {
            if($this->isVenueActive($venue)) {
                continue;
            }

            if($this->didTrialExpireWithinLast30DaysOrInFuture($venue)) {
                continue;
            }

            $venue->delete();
        }
    }

    // Checks if venue has ever had orders, has active subscription or data storage.
    private function isVenueActive(Tenant $venue): bool {
        $venueDataCollection = collect($this->venues);

        /** @var VenueData $venueData */
        $venueData = $venueDataCollection->firstWhere('id', $venue->id);

        if(!$venueData) {
            return false;
        }

        $hasActiveSubscription = $this->stripeService->venueHasActiveSubscription($venueData);

        if($hasActiveSubscription) {
            return true;
        }

        $hasDataStorage = $this->stripeService->checkDataStorageFor($venueData);

        if($hasDataStorage) {
            return true;
        }

        // If venue passed all above criteria, and they also didn't have orders within the last month,
        // then we should return false.
        return $venueData->hadOrdersWithinLastMonth();
    }

    private function didTrialExpireWithinLast30DaysOrInFuture(Tenant $venue): bool {
        $trialEndsAt = $venue->trialEndsAt;

        if($trialEndsAt === null) {
            return false;
        }

        $trialEndDate = CarbonImmutable::parse($venue->trialEndsAt);

        if($trialEndDate->isFuture()) {
            return true;
        }

        return $trialEndDate->diffInDays(CarbonImmutable::now()) < 30;
    }
}
