<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

enum AclPermission: string
{
    case IGNORE_CHURN_RISK_CALCULATION = 'ignore_churn_risk_calculation';
}
