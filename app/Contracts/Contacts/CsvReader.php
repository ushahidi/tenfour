<?php

namespace RollCall\Contracts\Contacts;

interface CsvReader extends Reader
{
    /**
     * @return array
     */
    public function fetchHeader();
    
}
