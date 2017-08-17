<?php

namespace RollCall\Messaging;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;

class PhoneNumberAdapter
{
    /**
     * @var \libphonenumber\PhoneNumber
     */
    private $number;

    /**
     * @var \libphonenumber\PhoneNumberToCarrierMapper
     */
    private $carrier_mapper;

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    private $util;

    /**
     * @param string $number
     * @param libphonenumber\PhoneNumberUtil $util
     * @param libphonenumber\PhoneNumberToCarrierMapper $carrier_mapper
     *
     * @throws \libphonenumber\NumberParseException if number cannot be parsed
     */
    public function __construct($number, PhoneNumberUtil $util, PhoneNumberToCarrierMapper $carrier_mapper)
    {
        // The assumption is that all phone numbers are passed as international numbers
        if (! starts_with($number, '+')) {
            $number = '+'.$number;
        }

        $this->number = $util->parse($number, null);
        $this->util = $util;
        $this->carrier_mapper = $carrier_mapper;
    }

    public function getCountryCode()
    {
        return $this->number->getCountryCode();
    }

    public function getNationalNumber()
    {
        return $this->number->getNationalNumber();
    }

    public function getCarrier()
    {
        return $this->carrier_mapper->getNameForNumber($this->number, 'en');
    }

    public function getRegionCode()
    {
        return $this->util->getRegionCodeForNumber($this->number);
    }

    public function getNormalizedNumber()
    {
        return $this->util->format($this->number, PhoneNumberFormat::E164);
    }
}
