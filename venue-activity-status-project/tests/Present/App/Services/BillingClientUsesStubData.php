<?php

declare(strict_types=1);

namespace Tests\Present\App\Services;

use Infrastructure\Venue\BillingClient;

class BillingClientUsesStubData implements BillingClient
{
    public function __construct(private string $stubDataFileName)
    {
    }

    public function getInvoices(): array
    {
        return json_decode(
            file_get_contents(__DIR__ . '/' . $this->stubDataFileName),
            false,
        )->data;
    }

    public function getSubscriptions(): array
    {
        return json_decode(
            file_get_contents(__DIR__ . '/' . $this->stubDataFileName),
            false,
        )->data;
    }
}
