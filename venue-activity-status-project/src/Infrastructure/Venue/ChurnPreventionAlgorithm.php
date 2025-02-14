<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;

final class ChurnPreventionAlgorithm implements ShouldQueue
{
    public function __construct(
        private readonly Repository $configRepository,
    ) {
    }

    public function handle(VenueDataChurnRiskQueryObject $churnRiskQueryObject): void
    {
        foreach ($churnRiskQueryObject->query() as $venue) {
            if ($venue->churnRiskStatus->shouldNotBeNotified()) {
                continue;
            }

            (new AnonymousNotifiable())->route(
                'slack',
                $this->configRepository->get('core.slack_churn_prevention_webhook_url'),
            )->notify(
                new ChurnPreventionNotification($this->configRepository, $venue),
            );
        }
    }
}
