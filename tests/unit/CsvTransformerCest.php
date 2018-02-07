<?php

use TenFour\Contacts\CsvTransformer;

class CsvTransformerCest
{
    public function testTransformFields(UnitTester $t)
    {
        $map = [
            0 => 'name',
            2 => 'phone',
            3 => 'email',
            5 => 'twitter',
        ];

        $fields = ['Linda', 'software developer', '254923333300', 'linda@ushahidi.com', 'P.O. Box 42, Nairobi', '@lk'];

        $transformer = new CsvTransformer($map);

        $transformed_fields = $transformer->transform($fields);

        $t->assertEquals($transformed_fields, [
            'name'    => 'Linda',
            'phone'   => '254923333300',
            'email'   => 'linda@ushahidi.com',
            'twitter' => '@lk',
        ]);
    }
}
