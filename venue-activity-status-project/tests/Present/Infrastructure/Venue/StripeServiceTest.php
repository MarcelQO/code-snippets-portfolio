<?php

declare(strict_types=1);


use DateTimeImmutable;
use Infrastructure\Venue\StripeService;
use Infrastructure\Venue\VenueData;
use PHPUnit\Framework\TestCase;
use Tests\Present\App\Services\BillingClientUsesStubData;

final class StripeServiceTest extends TestCase
{
    public function testItCanCheckIfAVenueHasAnActiveSubscription(): void
    {
        $stripeService = new StripeService(
            new BillingClientUsesStubData('stripe_subscriptions.json'),
        );

        self::assertTrue(
            $stripeService->venueHasActiveSubscription(
                new VenueData(
                    1,
                    'cus_MatDIfdyIC4gZM',
                    'acct_1FRDq4IaggH0dDzX',
                    new DateTimeImmutable('2021-01-01'),
                ),
            ),
        );

        self::assertFalse(
            $stripeService->venueHasActiveSubscription(
                new VenueData(
                    1,
                    'fake_stripe_id',
                    'acct_1FRDqhAVLAGyPgE4',
                    new DateTimeImmutable('2021-01-01'),
                ),
            ),
        );

        // Test if SM subscription is counted as active subscription
        self::assertFalse(
            $stripeService->venueHasActiveSubscription(
                new VenueData(
                    1,
                    'cus_LcDeKgBjjdU6SC',
                    'acct_1FRDqhAVLAGyPgE4',
                    new DateTimeImmutable('2021-01-01'),
                ),
            ),
        );
    }

    
    public function testItCanCheckIfAVenueHasPaidAllInvoices(): void
    {
        $stripeService = new StripeService(
            new BillingClientUsesStubData('stripe_invoices.json'),
        );

        // Should return false because venue has no unpaid invoices, so the stripeId is not present in stripe_invoices
        self::assertFalse(
            $stripeService->venueHasUnpaidInvoice(
                new VenueData(
                    1,
                    'cus_MatDIfdyIC4gZM',
                    'acct_1FRDq4IaggH0dDzX',
                    new DateTimeImmutable('2021-01-01'),
                ),
            ),
        );

        // Should return true because venue has unpaid invoices
        self::assertTrue(
            $stripeService->venueHasUnpaidInvoice(
                new VenueData(
                    1,
                    'cus_LcDeKgBjjdU6SC',
                    'acct_1FRDqhAVLAGyPgE4',
                    new DateTimeImmutable('2021-01-01'),
                ),
            ),
        );
    }
}
