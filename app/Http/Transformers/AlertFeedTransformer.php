<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class AlertFeedTransformer extends TransformerAbstract
{

    public function transform(array $alert)
    {
        return $alert;
    }
}
