<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{
    public function transform(array $region)
    {
        return $region;
    }
}
