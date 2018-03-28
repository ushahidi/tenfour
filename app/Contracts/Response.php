<?php

namespace TenFour\Contracts;

interface Response
{
    /**
     * Create response for a single item using a transformer and optional resource for the namespace
     *
     * @param array $output
     * @param object $transformer
     * @param string $resource
     *
     * @return \Dingo\Api\Http\Response
     */
    public function item(array $output, $transformer, $resource);

    /**
     * Create response for a collection using a transformer and optional resource for the namespace
     *
     * @param array $output
     * @param object $transformer
     * @param string $resource
     *
     * @return \Dingo\Api\Http\Response
     */
    public function collection(array $output, $transformer, $resource);
}
