<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class ScheduledCheckInTransformer extends TransformerAbstract
{

    public function transform(array $scheduled_check_in)
    {
        $scheduled_check_in['id'] =  (int) $scheduled_check_in['id'];
        return $scheduled_check_in;
    }

    // FIXME: the transformer for check ins throws an error for some reason?
    // we send an array but it ends up as an int (its id) somewhere down the line 
    // /**
    //  * List of resources possible to include
    //  *
    //  * @var array
    //  */
    // protected $defaultIncludes = [
    //     'check_ins'
    // ];
    // /**
    //  * Include checkins
    //  *
    //  * @return League\Fractal\ItemResource
    //  */
    // public function includeCheckIns(array $scheduled_check_in)
    // {
    //     $check_in = $scheduled_check_in['check_ins'];
    //     return $this->collection($check_in, new CheckInTransformer);
    // }

}
