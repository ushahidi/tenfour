<?php

use RollCall\Messaging\AnswerParser;
use Codeception\Util\Stub;

class AnswerParserCest
{

    public function __construct() {
    }

    public function testDefaultYesNoCaseInsensitive(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes"],
            ["answer" => "No"]
        ];

        $message = " yes ";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $answers[0]['answer']
        );
    }

    public function testDefaultYesNoBothAnswers(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes"],
            ["answer" => "No"]
        ];

        $message = " yes no ";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $answers[0]['answer']
        );
    }

    public function testDefaultYesNoOtherAnswer(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes"],
            ["answer" => "No"]
        ];

        $message = " maybe ";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $message
        );
    }

    public function testNoAnswers(UnitTester $t)
    {
        $answers = [];

        $message = "yes";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $message
        );
    }

    public function testCustomThreeAnswers(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes"],
            ["answer" => "No"],
            ["answer" => "Maybe"]
        ];

        $message = " maybe ";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $answers[2]['answer']
        );
    }

    public function testCustomMultipleWordsOtherAnswer(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes I do"],
            ["answer" => "No I don't"],
            ["answer" => "Mean maybe"]
        ];

        $message = " yes ";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $message
        );
    }

    public function testCustomMultipleWords(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes I do"],
            ["answer" => "No I don't"],
            ["answer" => "Mean maybe"]
        ];

        $message = " yes i do no i don't mean maybe";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $answers[0]['answer']
        );
    }

    public function testDefaultYesNoMultipleAnswersInMessage(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes"],
            ["answer" => "No"]
        ];

        $message = " yes no yes ";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $answers[0]['answer']
        );
    }

    public function testDefaultYesNoWordBoundaries(UnitTester $t)
    {
        $answers = [
            ["answer" => "Yes"],
            ["answer" => "No"]
        ];

        $message = "yesnoyes";

        $t->assertEquals(
          AnswerParser::parse($message, $answers),
          $message
        );
    }
}
