<?php

namespace RollCall\Contacts;

use RollCall\Contracts\Contacts\CsvImporter as CsvImporterInterface;
use RollCall\Contracts\Repositories\ContactRepository;
use RollCall\Contracts\Repositories\PersonRepository;
use RollCall\Contracts\Contacts\CsvReader as CsvReaderInterface;
use RollCall\Contracts\Contacts\CsvTransformer as CsvTransformerInterface;
use DB;

class CsvImporter implements CsvImporterInterface
{
    /**
     * The organization id
     *
     * @var integer
     */
    private $organization_id;

    /**
     * The CSV reader
     *
     * @var object
     */
    private $reader;

    /**
     * The Contact repository
     *
     * @var object
     */
    private $contacts;

    /**
     * The Person repository
     *
     * @var object
     */
    private $people;

    /**
     * User fields
     *
     * @var array
     */
    private $user_fields = ['name', 'description'];

     /**
     * Contact fields
     *
     * @var array
     */
    private $contact_fields = ['email', 'twitter', 'phone'];

    public function __construct(CsvReaderInterface $reader, CsvTransformerInterface $transformer, ContactRepository $contacts, PersonRepository $people, $organization_id)
    {
        $this->reader = $reader;
        $this->contacts = $contacts;
        $this->people = $people;
        $this->transformer = $transformer;
        $this->organization_id = $organization_id;
    }

    public function import()
    {
        $count = 0;
        $rows = $this->reader->read();

        // Transform data for input and save it
        DB::transaction(function () use ($rows, &$count) {
            foreach($rows as $row)
            {
                $row = $this->transformer->transform($row);

                $contacts = array_except($row, $this->user_fields);
                $user_input = array_except($row, $this->contact_fields);

                // Save user details
                $person = $this->people->create($this->organization_id, $user_input);

                // Save contacts
                foreach ($contacts as $type => $contact)
                {
                    $contact_input = [
                        'contact' => $contact,
                        'type'    => $type,
                        'user_id' => $person['id'],
                    ];

                    $this->contacts->create($contact_input);
                }

                $count++;
            }
        });

        return $count;
    }
}
