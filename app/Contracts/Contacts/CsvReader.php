<?php

namespace TenFour\Contracts\Contacts;

interface CsvReader extends Reader
{
    /**
     * @return array
     */
    public function fetchHeader();
    
}
