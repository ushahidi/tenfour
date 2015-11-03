<?php

use Faker\Factory as Faker;

class OrganizationTableSeeder extends Seeder {

    public function run()
    {
        $faker = Faker::create();

        foreach(range(1,5) as $index)
        {
            Organization::create([
                'name' => $faker->word(2),
                'sub_domain' => $faker->word(1)
            ]);
        }
    }
}
