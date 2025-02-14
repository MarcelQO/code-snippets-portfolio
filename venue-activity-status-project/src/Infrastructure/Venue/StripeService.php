<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

class StripeService
{
    private const DATA_STORAGE_PRODUCT_IDS = [
        'prod_HAqBXXXXXXXXXX', // Stripe test environment
        'prod_HHYBXXXXXXXXXX', // DK
        'prod_HHYGXXXXXXXXXX', // NO
        'prod_HHZBXXXXXXXXXX', // UK
    ];

    private const SM_PRODUCT_IDS = [
        'prod_G6BiXXXXXXXXXX', // Stripe live environment
        'prod_G0CAXXXXXXXXXX', // Stripe test environment
    ];
    
    private array $subscriptions;
    private array $invoices;

    public function __construct(private readonly BillingClient $stripeClient)
    {
        $this->subscriptions = $this->stripeClient->getSubscriptions();
        $this->invoices = $this->stripeClient->getInvoices();
    }

    public function venueHasActiveSubscription(VenueData $venueData): bool
    {
        $activeSubscriptions = $this->getSubscriptionsFor($venueData);

        if (self::venueHasDataStorage($activeSubscriptions)) {
            return count($activeSubscriptions) >= 2;
        }

        if (self::venueHasStaffManagerSubscription($activeSubscriptions)) {
            return count($activeSubscriptions) >= 2;
        }

        return count($activeSubscriptions) > 0;
    }

    public function venueHasUnpaidInvoice(VenueData $venueData): bool
    {
        return count($this->getInvoicesFor($venueData)) > 0;
    }

    private static function venueHasDataStorage($subscriptions): bool
    {
        foreach ($subscriptions as $subscription) {
            foreach ($subscription->items->data as $item) {
                if (in_array($item->plan->product, self::DATA_STORAGE_PRODUCT_IDS, true)) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function venueHasStaffManagerSubscription($subscriptions): bool
    {
        foreach ($subscriptions as $subscription) {
            foreach ($subscription->items->data as $item) {
                if (in_array($item->plan->product, self::SM_PRODUCT_IDS, true)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getSubscriptionsFor(VenueData $venueData): array
    {
        return array_filter(
            $this->subscriptions,
            static fn (object $subscription) => $subscription->customer === $venueData->stripeId,
        );
    }

    private function getInvoicesFor(VenueData $venueData): array
    {
        return array_filter(
            $this->invoices,
            static fn (object $invoice) => $invoice->customer === $venueData->stripeId,
        );
    }

    public function checkDataStorageFor(VenueData $venueData): bool
    {
        $activeSubscriptions = $this->getSubscriptionsFor($venueData);

        return self::venueHasDataStorage($activeSubscriptions);
    }
}
