<?php

namespace RollCall\Validators;

class ReservedWordValidator
{
    public function validateName($attr, $value, $params)
    {
        if (in_array($value, config('rollcall.reserved_words'))) {
            return false;
        }

        return true;
    }
}
