<?php

namespace TenFour\Contacts;

use TenFour\Contracts\Contacts\CsvTransformer as CsvTransformerInterface;


class CsvTransformer implements CsvTransformerInterface
{
    /**
     * The fields to map to
     *
     * @var array
     */
    private $map;

    public function __construct($map)
    {
        $this->map = $map;
    }

    public function transform($data)
    {
        $transformed_data = [];

        foreach($data as $index => $field)
        {
            if (isset($this->map[$index])) {
                $key = $this->map[$index];
                $transformed_data[$key] = $field;
            }
        }

        return $transformed_data;
    }
}
