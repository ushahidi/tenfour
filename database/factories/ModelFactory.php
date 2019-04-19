<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(TenFour\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'terms_of_service' => 1,
        'source' => 'local',
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(\TenFour\Models\Organization::class, function (Faker\Generator $faker) {
    return [
        'name' => 'My org',
        'subdomain' => 'myorg.tenfour.test.local',
    ];
});


$factory->define(\TenFour\Models\CheckIn::class, function (Faker\Generator $faker) {
    return [
        'message' => $faker->name,
        'user_id' => factory(\TenFour\Models\User::class)->create()->id,
        'organization_id' => factory(\TenFour\Models\Organization::class)->create()->id,
        'send_via' => json_encode('email'),
        'template' => true,
        'answers' => json_encode([
            ['answer'=>'No','color'=>'#BC6969','icon'=>'icon-exclaim','type'=>'negative'],
            ['answer'=>'Yes','color'=>'#E8C440','icon'=>'icon-check','type'=>'positive']
          ])
    ];
});

$factory->define(\TenFour\Models\ScheduledCheckIn::class, function (Faker\Generator $faker) {
    return [
        'starts_at' => '2019-04-18 20:53:09',
        "expires_at" => '2019-04-19 20:53:01',
        'frequency' => 'hourly'
    ];
});