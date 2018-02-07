<?php

namespace TenFour\Contracts\Contacts;

interface Transformer
{
    /**
     * @param array|object $data The contact data to be transformed.
     * @return mixed
     */
    public function transform($data);
}
