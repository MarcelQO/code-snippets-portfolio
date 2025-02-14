<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

use Illuminate\Contracts\Config\Repository;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\Stripe;
use Stripe\Subscription;

final class BillingClientUsesStripe implements BillingClient
{
    private array $stripeCountryIds;
    public function __construct(
        Repository $configRepository,
        private readonly LoggerInterface $logger,
    ) {
        Stripe::setApiKey($configRepository->get('present.stripe.stripe_secret'));
        $this->stripeCountryIds = array_filter([
            $configRepository->get('present.stripe.stripe_countryid_no'),
            $configRepository->get('present.stripe.stripe_countryid_dk'),
        ]);
    }

    public function getSubscriptions(): array
    {
        return array_reduce(
            $this->stripeCountryIds,
            fn (array $carry, string $current) => [...$carry, ...$this->getSubscriptionsForCountry($current)],
            [],
        );
    }

    private function getSubscriptionsForCountry(string $stripeCountryId): array
    {
        try {
            $stripeSubscriptions = Subscription::all(
                [
                    'limit' => 100,
                    'status' => 'active',
                ],
                [
                    'stripe_account' => $stripeCountryId,
                ],
            );

            $subscriptions = [];
            foreach ($stripeSubscriptions->autoPagingIterator() as $current) {
                $subscriptions[] = $current;
            }

            return $subscriptions;
        } catch (ApiErrorException $e) {
            $this->logger->error(sprintf(
                'An error occurred interacting with Stripe when trying to fetch subscriptions: "%s".',
                $e->getMessage(),
            ));

            throw new RuntimeException('Could not fetch subscriptions from Stripe.');
        }
    }

    public function getInvoices(): array
    {
        return array_reduce(
            $this->stripeCountryIds,
            fn (array $carry, string $current) => [...$carry, ...$this->getOpenInvoicesForCountry($current)],
            [],
        );
    }

    private function getOpenInvoicesForCountry(string $stripeCountryId): array
    {
        try {
            return iterator_to_array(
                Invoice::all(
                    [
                        'limit' => 100,
                        'status' => 'open',
                    ],
                    [
                        'stripe_account' => $stripeCountryId,
                    ],
                )->autoPagingIterator(),
            );
        } catch (ApiErrorException $e) {
            $this->logger->error(sprintf(
                'An error occurred interacting with Stripe when trying to fetch invoices: "%s".',
                $e->getMessage(),
            ));

            throw new RuntimeException('Could not fetch invoices from Stripe.');
        }
    }
}
