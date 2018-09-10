<?php

use TenFour\Channels\CheckInSMS;
use Codeception\Util\Stub;

class ConcatinatedSMSCest
{

    public function __construct() {
        $this->checkInSMSChannel = new CheckInSMS([]);
    }

    public function testIsURLOnSMSBoundary(UnitTester $t)
    {
        $data = [
          'msg'           => 'loooooooooooooooooooooong',
          'answers'       => [
            ['answer'=>'No','color'=>'#BC6969','icon'=>'icon-exclaim','type'=>'negative'],
            ['answer'=>'Yes','color'=>'#E8C440','icon'=>'icon-check','type'=>'positive']
          ],
          'keyword'       => 'tenfour',
          'check_in_url'  => 'http://testingsubdomain.ushahidi.com/checkins/1/answer/2',
          'org_name'      => '',
          'sender_name'   => ''
        ];

        $t->assertTrue(
          $this->checkInSMSChannel->isURLOnSMSBoundary('sms.checkin', $data)
        );
    }

    public function testIsURLNotOnSMSBoundary(UnitTester $t)
    {
        $data = [
          'msg'           => 'short',
          // 'answers'       => [],
          'keyword'       => 'tenfour',
          'check_in_url'  => 'http://testingsubdomain.ushahidi.com/checkins/1/answer/2',
          'org_name'      => '',
          'sender_name'   => ''
        ];

        $t->assertFalse(
          $this->checkInSMSChannel->isURLOnSMSBoundary('sms.checkin', $data)
        );
    }
}
