<?php

declare(strict_types=1);


namespace Infrastructure\Http\Controllers\Nova;

use Application\Shared\AuthenticatedVenueProvider;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Infrastructure\Venue\CheckChurnRiskWhitelistQueryObject;
use Infrastructure\Venue\UpdateChurnRiskWhitelistQueryObject;

class ToggleChurnRiskWhitelistController
{
    public function __construct(
        private readonly AuthenticatedVenueProvider          $authenticatedVenueProvider,
        private readonly UpdateChurnRiskWhitelistQueryObject $updateChurnRiskWhitelistQueryObject,
        private readonly CheckChurnRiskWhitelistQueryObject  $checkChurnRiskWhitelistQueryObject,
        private readonly ResponseFactory                     $responseFactory,
    )
    {
    }

    public function __invoke(): JsonResponse
    {
        $venueId = $this->authenticatedVenueProvider->provideIdOfAuthenticatedVenue();

        if ($this->checkChurnRiskWhitelistQueryObject->isVenueAlreadyInChurnRiskWhitelist($venueId)) {
            $this->updateChurnRiskWhitelistQueryObject->removeVenueFromChurnRiskWhitelist($venueId);
            $this->updateChurnRiskWhitelistQueryObject->setChurnRiskBadgeAsLow($venueId);
        } else {
            $this->updateChurnRiskWhitelistQueryObject->addVenueToChurnRiskWhitelist($venueId);
            $this->updateChurnRiskWhitelistQueryObject->setChurnRiskBadgeAsIgnored($venueId);
        }
        return $this->responseFactory->json(null, 200);
    }
}
