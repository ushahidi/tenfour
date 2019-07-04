<?php

use TenFour\Services\CreditService;
use Codeception\Util\Stub;

class CreditServiceCest
{
    const base_balance = 178;

    public function __construct() {
        $this->creditService = new CreditService();
    }

    public function testGetBalance(UnitTester $t)
    {
        $org_id = 2;

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            self::base_balance
        );
    }

    public function testAddCreditAdjustment(UnitTester $t)
    {
        $org_id = 2;

        $this->creditService->addCreditAdjustment($org_id, -1);

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            self::base_balance-1
        );
    }

    public function testAddCreditAdjustmentOverdrawn(UnitTester $t)
    {
        $org_id = 2;

        $this->creditService->addCreditAdjustment($org_id, -(self::base_balance+4));

        // allow credits to be overdrawn

        $t->assertEquals(
            $this->creditService->getBalance($org_id),
            -4
        );
    }

}
