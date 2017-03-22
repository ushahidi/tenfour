<?php

namespace RollCall\Validators;

use libphonenumber\PhoneNumberUtil;

class PhoneNumberValidator
{
    public function __construct()
    {

    }

    public function validatePhoneNumber($attr, $value, $params)
    {
        // Get region code. The assumption is that all phone numbers are passed as
        // international numbers
        if (! starts_with($value, '+')) {
            $value = '+'.$value;
        }

        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($value, null);
        return $phoneNumberUtil->isValidNumber($phoneNumberObject)
          && preg_match("/^\+?[\-\d\ ()]+$/", $value);
    }
}
