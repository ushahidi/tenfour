<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RollcallAnswersToObjects extends Migration
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
                 $old_answers = json_decode($roll_call->answers);
                 $answers = [];

                 if (!$old_answers || count($old_answers) == 0) {
                   continue;
                 }

                 foreach ($old_answers as $answer) {
                     if ($answer == 'No') {
                         $color = '#BC6969';
                         $icon = 'icon-exclaim';
                         $custom = false;
                     } else if ($answer == 'Yes') {
                         $color = '#E8C440';
                         $icon = 'icon-check';
                         $custom = false;
                     } else {
                         $color = '#4CBFCE';
                         $icon = '';
                         $custom = true;
                     }

                     array_push($answers, [
                       "answer" => $answer,
                       "color" => $color,
                       "icon" => $icon,
                       "custom" => $custom,
                     ]);
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
                 $new_answers = json_decode($roll_call->answers);
                 $answers = [];

                 if (!$new_answers || count($new_answers) == 0) {
                   continue;
                 }

                 foreach ($new_answers as $answer) {
                     array_push($answers, $answer->answer);
                 }

                 DB::table('roll_calls')->where('id', $roll_call->id)->update([
                   'answers' => json_encode($answers)
                 ]);
             }
         }
    }
}
