<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

interface BillingClient
{
    public function getSubscriptions(): array;

    public function getInvoices(): array;
}
