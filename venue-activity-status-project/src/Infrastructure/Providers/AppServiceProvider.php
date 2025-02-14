<?php

declare(strict_types=1);


use Illimunate\Support\ServiceProvider;
use Infrastructure\Venue\BillingClient;
use Infrastructure\Venue\BillingClientUsesStripe;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(BillingClient::class, BillingClientUsesStripe::class);
    }
}
