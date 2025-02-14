<?php

declare(strict_types=1);

use Infrastructure\Http\Controllers\Nova\ToggleChurnRiskWhitelistController;
use Illuminate\Routing\Router;

/** @var Router $router */
$router->middleware(['auth.check', 'auth.attempt'])->group(static function (Router $router) {
    $router->patch('/churn-risk/toggle-whitelist', ToggleChurnRiskWhitelistController::class);
});
