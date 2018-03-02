<?php

use TenFour\Messaging\PhoneNumberAdapter;
use Codeception\Util\Stub;
use Codeception\Stub\Expected;

class PhoneNumberAdapterCest
{
    private $phone_number_adapter;

    public function __construct() {
        $phone_number_util = Stub::make(
            libphonenumber\PhoneNumberUtil::class,
            [
                'parse' => Stub::make(
                    libphonenumber\PhoneNumber::class,
                    [
                        'getCountryCode' => Expected::once(function () {
                            return '254';
                        }),
                        'getNationalNumber' => Expected::once(function () {
                            return '722123456';
                        })
                    ]
                ),
                'format' => Expected::once(function () {
                    return '+254722123456';
                }),
                'getRegionCodeForNumber' => Expected::once(function () {
                    return 'KE';
                })
            ]
        );

        $carrier_mapper = Stub::make(
            libphonenumber\PhoneNumberToCarrierMapper::class,
            [
                'getNameForNumber' => Expected::once(function () {
                    return 'Safaricom';
                })
            ]
        );

        $this->phone_number_adapter = new PhoneNumberAdapter($phone_number_util, $carrier_mapper);
        $this->phone_number_adapter->setRawNumber('254722123456');
    }

    public function getCountryCode(UnitTester $t)
    {
        $t->assertEquals($this->phone_number_adapter->getCountryCode(),
                         '254');
    }

    public function getNationalNumber(UnitTester $t)
    {
        $t->assertEquals($this->phone_number_adapter->getNationalNumber(),
                         '722123456');
    }

    public function getCarrier(UnitTester $t)
    {
        $t->assertEquals($this->phone_number_adapter->getCarrier(),
                         'Safaricom');
    }

    public function getRegionCode(UnitTester $t)
    {
        $t->assertEquals($this->phone_number_adapter->getRegionCode(),
                         'KE');
    }

    public function testNormalization(UnitTester $t)
    {
        $t->assertEquals($this->phone_number_adapter->getNormalizedNumber(),
                         '+254722123456');
    }
}
