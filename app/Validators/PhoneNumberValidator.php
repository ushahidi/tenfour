<?php

namespace RollCall\Validators;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class PhoneNumberValidator
{
    public function validatePhoneNumber($attr, $value, $params)
    {
        // Get region code. The assumption is that all phone numbers are passed as
        // international numbers
        if (! starts_with($value, '+')) {
            $value = '+'.$value;
        }

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumberObject = $phoneNumberUtil->parse($value, null);
        } catch (NumberParseException $exception) {
            return false;
        }

        return $phoneNumberUtil->isValidNumber($phoneNumberObject)
          && preg_match("/^\+?[\-\d\ ()]+$/", $value);
    }
}
