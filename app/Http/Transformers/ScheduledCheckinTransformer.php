<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class ScheduledCheckinTransformer extends TransformerAbstract
{

    public function transform(array $scheduled_checkin)
    {
        $scheduled_checkin['id'] =  (int) $scheduled_checkin['id'];
        return $scheduled_checkin;
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
    // public function includeCheckIns(array $scheduled_checkin)
    // {
    //     $check_in = $scheduled_checkin['check_ins'];
    //     return $this->collection($check_in, new CheckInTransformer);
    // }

}
