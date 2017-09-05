<?php

use RollCall\Services\CreditService;
use Codeception\Util\Stub;

class CreditServiceCest
{

    public function __construct() {
        $this->creditService = new CreditService();
    }

    public function testGetBalance(UnitTester $t)
    {
        $org_id = 2;

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            3
        );
    }

    public function testAddCreditAdjustment(UnitTester $t)
    {
        $org_id = 2;

        $this->creditService->addCreditAdjustment($org_id, -1);

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            2
        );
    }

    public function testAddCreditAdjustmentOverdrawn(UnitTester $t)
    {
        $org_id = 2;

        $this->creditService->addCreditAdjustment($org_id, -4);

        // allow credits to be overdrawn

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            -1
        );
    }

    public function testExpireCreditsOnUnpaid(UnitTester $t)
    {
        $org_id = 2;

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            3
        );

        $this->creditService->expireCreditsOnUnpaid();

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            0
        );
    }


}
