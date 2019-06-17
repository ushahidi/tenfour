<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class AlertSourceTransformer extends TransformerAbstract
{

    public function transform(array $alert)
    {
        unset($alert['authentication_options']);
        return $alert;
    }
}
