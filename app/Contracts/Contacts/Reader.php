<?php

namespace RollCall\Contracts\Contacts;

interface Reader
{
    /**
     * @return array
     */
    public function read();
}
