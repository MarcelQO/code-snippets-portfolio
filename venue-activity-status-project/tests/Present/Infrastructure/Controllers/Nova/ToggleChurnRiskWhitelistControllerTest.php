<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Infrastructure\Venue\AclPermission;
use Tests\Legacy\IntegrationTestCase;

class ToggleChurnRiskWhitelistControllerTest extends IntegrationTestCase
{
    use DatabaseTransactions;

    private $aclPermissionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aclPermissionId = $this->app->make('db')->table('acl_permissions')
            ->where('name', AclPermission::IGNORE_CHURN_RISK_CALCULATION->value)
            ->first()
            ->id;
    }

    public function testItCanToggleChurnRiskWhitelist(): void
    {
        $this->assertDatabaseMissing('acl_permission_venue', [
            'acl_permission_id' => $this->aclPermissionId,
            'venue_id' => self::$tenant->uuid,
        ]);

        $this->patch('/nova/churn-risk/toggle-whitelist');

        $this->assertDatabaseHas('acl_permission_venue', [
            'acl_permission_id' => $this->aclPermissionId,
            'venue_id' => self::$tenant->uuid,
        ]);

        $this->patch('/nova/churn-risk/toggle-whitelist');

        $this->assertDatabaseMissing('acl_permission_venue', [
            'acl_permission_id' => $this->aclPermissionId,
            'venue_id' => self::$tenant->uuid,
        ]);
    }
}
