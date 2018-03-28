<?php

use Codeception\Util\Stub;
use Codeception\Stub\Expected;
use TenFour\Contracts\Contacts\CsvReader;
use TenFour\Contracts\Contacts\CsvTransformer;
use TenFour\Contacts\CsvImporter;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\PersonRepository;

class CsvImporterCest
{
    private $csv_importer;

    public function __construct()
    {
        //Reader
        $csv_reader = Stub::makeEmpty(CsvReader::class, [
            'fetchHeader' => function () {
                return ['name', 'description', 'email',  'phone'];
            },

            'read' => function () {
                return [
                    ['Mary', 'designer', '254722111111', 'mary@ushahidi.com', 'MV Building, Waiyaki Way', '@md'],
                    ['Linda', 'software developer', '254722111222', 'linda@ushahidi.com', 'P.O. Box 42, Nairobi', '@lk'],
                ];
            }
        ]);

        // Transformer
        $transformer = Stub::makeEmpty(CsvTransformer::class, [
            'transform' => function($row) {
                $transformed_data = [];

                foreach ($row as $index => $col)
                {
                    if ($index === 0) {
                        $transformed_data['name'] = $col;
                    } else if ($index === 2) {
                        $transformed_data['phone'] = $col;
                    } else if ($index === 3) {
                        $transformed_data['email'] = $col;
                    }
                }

                return $transformed_data;
            }
        ]);

        // Contact repository
        $contacts = Stub::makeEmpty(ContactRepository::class, [
            'create' => Expected::exactly(4, function() {})
        ]);

        // Person repository;
        $people = Stub::makeEmpty(PersonRepository::class, [
            'create' => Expected::exactly(2, function() {})
        ]);

        $this->csv_importer = new CsvImporter();
        $this->csv_importer->setReader($csv_reader);
        $this->csv_importer->setTransformer($transformer);
        $this->csv_importer->setContacts($contacts);
        $this->csv_importer->setPeople($people);
    }

    public function testImport(UnitTester $t)
    {
        $members = $this->csv_importer->import();

        $t->assertEquals(count($members), 2);
    }
}
