<?php

use RollCall\Jobs\SendRollCall;
use Codeception\Util\Stub;

class ConcatinatedSMSCest
{

    public function __construct() {
        $this->sendRollCall = new SendRollCall([]);
    }

    public function testIsURLOnSMSBoundary(UnitTester $t)
    {
        $data = [
          'msg'           => 'loooooooooooooooooooooong',
          'answers'       => ['Yes', 'No'],
          'keyword'       => 'rollcall',
          'rollcall_url'  => 'http://testingsubdomain.ushahidi.com/rollcalls/1/answer/2'
        ];

        $t->assertTrue(
          $this->sendRollCall->isURLOnSMSBoundary('sms.rollcall', $data)
        );
    }

    public function testIsURLNotOnSMSBoundary(UnitTester $t)
    {
        $data = [
          'msg'           => 'short',
          // 'answers'       => [],
          'keyword'       => 'rollcall',
          'rollcall_url'  => 'http://testingsubdomain.ushahidi.com/rollcalls/1/answer/2'
        ];

        $t->assertFalse(
          $this->sendRollCall->isURLOnSMSBoundary('sms.rollcall', $data)
        );
    }
}
