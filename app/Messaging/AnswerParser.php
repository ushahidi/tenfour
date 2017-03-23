<?php

namespace RollCall\Messaging;

use RollCall\Contracts\Repositories\RollCallRepository;

class AnswerParser
{
    public static function parse($message, array $expected_answers)
    {
        if (!isset($expected_answers) || count($expected_answers) == 0) {
            return $message;
        }

        $return = $message;
        $message = trim($message);
        $lowest = strlen($message);

        foreach ($expected_answers as $answer) {
            preg_match('/\b' . $answer['answer'] . '\b/i', $message, $matches, PREG_OFFSET_CAPTURE);

            if ($matches && count($matches)) {
              $pos = $matches[0][1];

              if ($pos !== FALSE && $pos < $lowest) {
                  $lowest = $pos;
                  $return = $answer['answer'];
              }
            }
        }

        return $return;
    }
}
