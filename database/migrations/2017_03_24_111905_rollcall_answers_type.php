<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RollcallAnswersType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         foreach (DB::table('roll_calls')->get() as $roll_call) {
             if ($roll_call->answers) {
                  $answers = json_decode($roll_call->answers);

                  foreach ($answers as $answer) {
                      if (!isset($answer->answer) || isset($answer->type)) {
                        continue;
                      }

                      if ($answer->answer === 'Yes') {
                          $answer->type = 'positive';
                      } else if ($answer->answer === 'No') {
                          $answer->type = 'negative';
                      } else {
                          $answer->type = 'custom';
                      }

                      unset($answer->custom);
                  }

                  DB::table('roll_calls')->where('id', $roll_call->id)->update([
                    'answers' => json_encode($answers)
                  ]);
             }
         }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         foreach (DB::table('roll_calls')->get() as $roll_call) {
             if ($roll_call->answers) {
                  $answers = json_decode($roll_call->answers);

                  foreach ($answers as $answer) {
                      if (!isset($answer->type) || isset($answer->custom)) {
                          continue;
                      }

                      if ($answer->type === 'custom') {
                          $answer->custom = true;
                      } else {
                          $answer->custom = false;
                      }

                      unset($answer->type);
                  }

                  DB::table('roll_calls')->where('id', $roll_call->id)->update([
                   'answers' => json_encode($answers)
                  ]);
             }
         }
    }
}
