<?php

use RollCall\Messaging\Validators\NexmoMessageValidator;
use RollCall\Messaging\InvalidMOMessageException;
use Illuminate\Http\Request;
use Codeception\Util\Stub;

class NexmoValidationCest
{
    private $secret = 'secret';
    private $validator;

    public function __construct() {
        $this->validator = new NexmoMessageValidator($this->secret);
    }

    private function getParamsWithoutSig()
    {
        return [
            'msisdn'    => '125125125',
            'text'      => 'test MO',
            'messageId' => '00FFBB',
            // Message timestamp for message received just now
            // Timezone is UTC+0
            'message-timestamp' => date('Y-m-d H:i:s', (time() - date('Z')))
        ];
    }

    private function getParamsWithGoodSig()
    {
        return $this->getParamsWithoutSig() + [
            'sig' => $this->getSig()
        ];
    }

    private function getSig()
    {
        $params = $this->getParamsWithoutSig();

        ksort($params);

        return md5('&' .urldecode(http_build_query($params)) . $this->secret);
    }

    private function getParamsWithBadSig()
    {
        return $this->getParamsWithoutSig() + [
            'sig' => 'Bad Sig'
        ];
    }

    private function getParamsWithOldTimestamp()
    {
        return array_merge(
            $this->getParamsWithoutSig(),
            [
                'message-timestamp' => '2017-01-30 09:30:30',
                'sig' => 'signature'
            ]
        );
    }

    public function testValidSig(UnitTester $t)
    {
        $request = Stub::make(
            Request::class,
            [
                'all' => $this->getParamsWithGoodSig()
            ]
        );

        $t->assertTrue($this->validator->isValid($request));
    }

    public function testInvalidSig(UnitTester $t)
    {
        $request = Stub::make(
             Request::class,
             [
                 'all' => $this->getParamsWithBadSig()
             ]
         );

        $t->expectException(new InvalidMOMessageException('Invalid signature'), function () use ($request) {
                $this->validator->validate($request);
            });
    }

    public function testOldMessage(UnitTester $t)
    {
        $request = Stub::make(
            Request::class,
            [
                'all' => $this->getParamsWithOldTimestamp()
            ]
        );

        $t->expectException(new InvalidMOMessageException('Message is too old'), function () use ($request) {
                $this->validator->validate($request);
            });
    }

    public function testMissingSig(UnitTester $t)
    {
        $request = Stub::make(
            Request::class,
            [
                'all' => $this->getParamsWithoutSig()
            ]
        );

        $t->expectException(new InvalidMOMessageException('Message is unsigned'), function () use ($request) {
                $this->validator->validate($request);
            });
    }
}
