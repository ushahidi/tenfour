<?php

namespace RollCall\Contracts\Contacts;

interface Importer
{
    /**
     * @return int The number of records imported
     */
    public function import();
}
