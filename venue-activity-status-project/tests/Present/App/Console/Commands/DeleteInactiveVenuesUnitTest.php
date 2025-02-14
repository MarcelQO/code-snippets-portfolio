<?php

declare(strict_types=1);


use App\Console\Commands\DeleteInactiveVenues;
use App\Models\Tenants\Tenant;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Infrastructure\Venue\StripeService;
use Infrastructure\Venue\VenueData;
use Infrastructure\Venue\VenueDataQueryObject;
use Mockery;
use Tests\Present\TestCaseWithApplicationBootstrapping;

class DeleteInactiveVenuesUnitTest extends TestCaseWithApplicationBootstrapping
{
    use DatabaseTransactions;

    public function testIsVenueActiveWithInactiveSubscriptionAndNoDataStorageWithOrdersWithinLastMonth(): void
    {
        $stripeServiceMock = Mockery::mock(StripeService::class);
        $venueDataQueryObjectMock = Mockery::mock(VenueDataQueryObject::class);
        $tenant = \Tests\Present\App\Console\Commands\factory(Tenant::class)->create();

        // Creating random VenueData object with recent orders.
        $venueData = new VenueData($tenant->id, 'stripeId', 'stripeCountryId', new DateTimeImmutable());

        $venueDataQueryObjectMock->shouldReceive('query')->andReturn([$venueData]);
        $stripeServiceMock->shouldReceive('venueHasActiveSubscription')->with($venueData)->andReturn(false);
        $stripeServiceMock->shouldReceive('checkDataStorageFor')->with($venueData)->andReturn(false);

        $command = new DeleteInactiveVenues($stripeServiceMock);

        $command->handle($venueDataQueryObjectMock);

        $tenantFromDB = Tenant::withTrashed()->find($tenant->id);

        $this->assertNull($tenantFromDB->deleted);
    }

    public function testIsVenueActiveWithInactiveSubscriptionAndNoDataStorageWithNoOrdersWithinLastMonth(): void
    {
        $stripeServiceMock = Mockery::mock(StripeService::class);
        $venueDataQueryObjectMock = Mockery::mock(VenueDataQueryObject::class);
        $tenant = \Tests\Present\App\Console\Commands\factory(Tenant::class)->create();

        $orderDate = new DateTimeImmutable();

        // Creating random VenueData object with no orders for two months.
        $venueData = new VenueData($tenant->id, 'stripeId', 'stripeCountryId', $orderDate->sub(
            new \DateInterval('P2M'),
        ));

        $venueDataQueryObjectMock->shouldReceive('query')->andReturn([$venueData]);
        $stripeServiceMock->shouldReceive('venueHasActiveSubscription')->with($venueData)->andReturn(false);
        $stripeServiceMock->shouldReceive('checkDataStorageFor')->with($venueData)->andReturn(false);

        $command = new DeleteInactiveVenues($stripeServiceMock);
        $command->handle($venueDataQueryObjectMock);

        $tenantFromDB = Tenant::withTrashed()->find($tenant->id);

        $this->assertNotNull($tenantFromDB->deleted);
    }

    public function testIsVenueActiveWithInactiveSubscriptionAndNoDataStorageWithLatestOrderBeingNull(): void
    {
        $stripeServiceMock = Mockery::mock(StripeService::class);
        $venueDataQueryObjectMock = Mockery::mock(VenueDataQueryObject::class);
        $tenant = \Tests\Present\App\Console\Commands\factory(Tenant::class)->create();

        // Creating random VenueData object with null in latest order column.
        $venueData = new VenueData($tenant->id, 'stripeId', 'stripeCountryId', null);

        $venueDataQueryObjectMock->shouldReceive('query')->andReturn([$venueData]);
        $stripeServiceMock->shouldReceive('venueHasActiveSubscription')->with($venueData)->andReturn(false);
        $stripeServiceMock->shouldReceive('checkDataStorageFor')->with($venueData)->andReturn(false);

        $command = new DeleteInactiveVenues($stripeServiceMock);
        $command->handle($venueDataQueryObjectMock);

        $tenantFromDB = Tenant::withTrashed()->find($tenant->id);

        $this->assertNotNull($tenantFromDB->deleted);
    }

    public function testIsVenueActiveWithActiveDataStorage(): void
    {
        $stripeServiceMock = Mockery::mock(StripeService::class);
        $venueDataQueryObjectMock = Mockery::mock(VenueDataQueryObject::class);
        $tenant = \Tests\Present\App\Console\Commands\factory(Tenant::class)->create();

        $venueData = new VenueData($tenant->id, 'stripeId', 'stripeCountryId', new DateTimeImmutable());

        $venueDataQueryObjectMock->shouldReceive('query')->andReturn([$venueData]);
        $stripeServiceMock->shouldReceive('venueHasActiveSubscription')->with($venueData)->andReturn(false);
        $stripeServiceMock->shouldReceive('checkDataStorageFor')->with($venueData)->andReturn(true);

        $command = new DeleteInactiveVenues($stripeServiceMock);
        $command->handle($venueDataQueryObjectMock);

        $tenantFromDB = Tenant::withTrashed()->find($tenant->id);

        $this->assertNull($tenantFromDB->deleted);
    }

    public function testIsVenueActiveWithActiveSubscription(): void
    {
        $stripeServiceMock = Mockery::mock(StripeService::class);
        $venueDataQueryObjectMock = Mockery::mock(VenueDataQueryObject::class);
        $tenant = \Tests\Present\App\Console\Commands\factory(Tenant::class)->create();

        $venueData = new VenueData($tenant->id, 'stripeId', 'stripeCountryId', new DateTimeImmutable());

        $venueDataQueryObjectMock->shouldReceive('query')->andReturn([$venueData]);
        $stripeServiceMock->shouldReceive('venueHasActiveSubscription')->with($venueData)->andReturn(false);
        $stripeServiceMock->shouldReceive('checkDataStorageFor')->with($venueData)->andReturn(true);

        $command = new DeleteInactiveVenues($stripeServiceMock);
        $command->handle($venueDataQueryObjectMock);

        $tenantFromDB = Tenant::withTrashed()->find($tenant->id);

        $this->assertNull($tenantFromDB->deleted);
    }

    public function testIsVenueActiveWithActiveSubscriptionAndActiveDataStorage(): void
    {
        $stripeServiceMock = Mockery::mock(StripeService::class);
        $venueDataQueryObjectMock = Mockery::mock(VenueDataQueryObject::class);
        $tenant = \Tests\Present\App\Console\Commands\factory(Tenant::class)->create();

        $venueData = new VenueData($tenant->id, 'stripeId', 'stripeCountryId', new DateTimeImmutable());

        $venueDataQueryObjectMock->shouldReceive('query')->andReturn([$venueData]);
        $stripeServiceMock->shouldReceive('venueHasActiveSubscription')->with($venueData)->andReturn(true);
        $stripeServiceMock->shouldReceive('checkDataStorageFor')->with($venueData)->andReturn(true);

        $command = new DeleteInactiveVenues($stripeServiceMock);
        $command->handle($venueDataQueryObjectMock);

        $tenantFromDB = Tenant::withTrashed()->find($tenant->id);

        $this->assertNull($tenantFromDB->deleted);
    }
}
