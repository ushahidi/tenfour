<?php

namespace TenFour\Http;

use Dingo\Api\Routing\Helpers;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal;

use TenFour\Contracts\Response as ResponseInterface;
use TenFour\Serializers\CustomArraySerializer;

class Response implements ResponseInterface
{
    use Helpers;

    public function item(array $output, $transformer, $resource)
    {
        $resource = new Item($output, $transformer, $resource);
        return $this->format($resource);
    }

    public function collection(array $output, $transformer, $resource)
    {
        $resource = new Collection($output, $transformer, $resource);
        return $this->format($resource);
    }

    protected function format($resource)
    {
        $fractal = new Fractal\Manager();
        $fractal->setSerializer(new CustomArraySerializer());
        $data = $fractal->createData($resource)->toArray();
        return $this->response->array($data);
    }
}
