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

        $tokens = preg_split('/\s+/', $message);

        return array_first($tokens, function ($value, $key) use ($expected_answers) {
            return strcasecmp($value, $expected_answers[0]['answer']) === 0 || strcasecmp($value, $expected_answers[1]['answer']) === 0;
        }, null);
    }
}
