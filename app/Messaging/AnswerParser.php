<?php

namespace RollCall\Messaging;

use RollCall\Contracts\Repositories\RollCallRepository;

class AnswerParser
{
    public static function parse($message, array $expected_answers)
    {
        // We expect 2 answers. A 'no' and 'yes' answer if answers are available.
        if (count($expected_answers) < 2) {
            return null;
        }

        $tokens = preg_split('/\s+/', $this->message);

        return array_first($tokens, function ($value, $key) {
            return strcasecmp($value, $expected_answers[0]) === 0 || strcasecmp($value, $expected_answers[1]) === 0;
        }, null);
    }
}