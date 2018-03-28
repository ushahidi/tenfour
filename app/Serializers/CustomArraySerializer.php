<?php

namespace TenFour\Serializers;

use League\Fractal\Serializer\ArraySerializer;

class CustomArraySerializer extends ArraySerializer
{
    /**
     * Add an optional resource as a namespace to output
     *
     * @param string $resourceKey
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey) {
            return [
                $resourceKey => $data
            ];
        }

        return $data;
    }

    public function item($resourceKey, array $data)
    {
        return $this->collection($resourceKey, $data);
    }
}
