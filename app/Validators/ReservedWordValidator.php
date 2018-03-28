<?php

namespace TenFour\Validators;

class ReservedWordValidator
{
    public function validateName($attr, $value, $params)
    {
        if (in_array($value, config('tenfour.reserved_words'))) {
            return false;
        }

        return true;
    }
}
