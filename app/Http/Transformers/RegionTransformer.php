<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class RegionTransformer extends TransformerAbstract
{
    public function transform(array $region)
    {
        return $region;
    }
}
