<?php

namespace TenFour\Contacts;

use TenFour\Contracts\Contacts\CsvImporter as CsvImporterInterface;
use TenFour\Contracts\Repositories\ContactRepository;
use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Contracts\Contacts\CsvReader as CsvReaderInterface;
use TenFour\Contracts\Contacts\CsvTransformer as CsvTransformerInterface;
use DB;
use Validator;
use Exception;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberToCarrierMapper;
use Illuminate\Validation\ValidationException;

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
    private $contact_fields = ['email', 'twitter', 'phone', 'address', 'slack'];

    public function setReader(CsvReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    public function setTransformer(CsvTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function setContacts(ContactRepository $contacts)
    {
        $this->contacts = $contacts;
    }

    public function setPeople(PersonRepository $people)
    {
        $this->people = $people;
    }

    public function setOrganizationId($organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function import()
    {
        $rows = $this->reader->read();
        $members = [];

        // Transform data for input and save it
        DB::transaction(function () use ($rows, &$members) {
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
                    if (!$contact || empty($contact)) {
                        continue;
                    }

                    $validator = Validator::make([$type => $contact], [
                        'phone' => 'phone_number',
                        'email' => 'email'
                    ]);

                    try {
                        $validator->validate();
                    } catch (ValidationException $e) {
                        throw new CsvImportException(
                            'Contact "' . $user_input['name'] . '" has invalid ' .
                            $type . ' data "' . $contact . '". ' .
                            'Phone numbers must be in international format (e.g. +254723674180) and be ' .
                            'a real, existing number. Email addresses must be formatted correctly. '
                        );
                    }

                    if ($type == 'phone' && ! starts_with($contact, '+')) {
                        $contact = '+'.$contact;
                    }

                    $existing_contact = $this->contacts->getByContact($contact, $this->organization_id);

                    if ($existing_contact) {
                        throw new CsvImportException(
                            'A contact already exists with ' . $type . ' data "' . $contact . '" ' .
                            'TenFour will not import duplicate contacts. Please include only ' .
                            'new contacts in the CSV file.'
                        );
                    }

                    $contact_input = [
                        'contact' => $contact,
                        'type'    => $type,
                        'user_id' => $person['id'],
                        'organization_id' => $this->organization_id,
                    ];

                    // Store country code and national number
                    if ($type == 'phone') {
                        $number = PhoneNumberUtil::getInstance()
                                ->parse($contact, null);
                        $national_number = $number->getNationalNumber();
                        $country_code = $number->getCountryCode();
                        $carrier = PhoneNumberToCarrierMapper::getInstance()
                                       ->getNameForNumber($number, 'en');

                        $contact_input = $contact_input + [
                            'meta' => [
                                'national_number' => $national_number,
                                'country_code'    => $country_code,
                                'carrier'         => $carrier,
                            ]
                        ];
                    }

                    $this->contacts->create($contact_input);
                }

                array_push($members, $person['id']);
            }
        });

        //return $count;
        return $members;
    }
}
