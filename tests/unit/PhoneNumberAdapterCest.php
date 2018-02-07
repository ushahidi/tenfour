<?php

use TenFour\Messaging\PhoneNumberAdapter;
use Codeception\Util\Stub;

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
                        'getCountryCode' => Stub::once(function () {
                            return '254';
                        }),
                        'getNationalNumber' => Stub::once(function () {
                            return '722123456';
                        })
                    ]
                ),
                'format' => Stub::once(function () {
                    return '+254722123456';
                }),
                'getRegionCodeForNumber' => Stub::once(function () {
                    return 'KE';
                })
            ]
        );

        $carrier_mapper = Stub::make(
            libphonenumber\PhoneNumberToCarrierMapper::class,
            [
                'getNameForNumber' => Stub::once(function () {
                    return 'Safaricom';
                })
            ]
        );

        $this->phone_number_adapter = new PhoneNumberAdapter('254722123456', $phone_number_util, $carrier_mapper);
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
