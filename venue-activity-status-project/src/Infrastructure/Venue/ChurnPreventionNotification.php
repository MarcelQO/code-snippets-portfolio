<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

final class ChurnPreventionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Repository $configRepository,
        private readonly VenueChurnRiskData $venueChurnRiskData,
    ) {
    }

    public function via(): array
    {
        return ['slack'];
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage())
            ->from('Churn Prevention Bot')
            ->to($this->configRepository->get('core.slack_churn_prevention_webhook_url'))
            ->content($this->venueChurnRiskData->churnRiskStatus->getText())
            ->attachment(function (SlackAttachment $attachment): void {
                $attachment->title(
                    $this->venueChurnRiskData->churnRiskStatus === ChurnRiskStatus::HIGH
                        ? 'Inactivity AND Unpaid Invoices'
                        : 'Inactivity OR Unpaid Invoices',
                )
                ->fields([
                    'Restaurant' => $this->getRestaurantTitle(),
                    'Stripe ID' => $this->getStripeId(),
                    'Phone no.' => $this->getPhoneNumber(),
                    'Days since activity' => $this->venueChurnRiskData->daysSinceLastOrder(),
                ])
                ->color($this->venueChurnRiskData->churnRiskStatus->getColor());
            });
    }

    private function getRestaurantTitle(): string
    {
        return sprintf(
            '<%s/resources/POS-venues/%s|%s>',
            $this->configRepository->get('core.nova_base_url'),
            $this->venueChurnRiskData->venueId,
            $this->venueChurnRiskData->venueName,
        );
    }

    private function getStripeId(): string
    {
        return sprintf(
            '<https://dashboard.stripe.com/%s/customers/%s|%s>',
            $this->venueChurnRiskData->stripeCountryId,
            $this->venueChurnRiskData->stripeId,
            $this->venueChurnRiskData->stripeId,
        );
    }


    private function getPhoneNumber(): string
    {
        if ($this->venueChurnRiskData->venuePhone === null || $this->venueChurnRiskData->venuePhone === '') {
            return 'No phone number';
        }

        return sprintf(
            '<tel:%s|%s>',
            $this->venueChurnRiskData->venuePhone,
            $this->venueChurnRiskData->venuePhone,
        );
    }
}
