<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Http\Response;
use Illuminate\Http\Request;
use TenFour\Http\Transformers\UserLocationTransformer;

class UserLocationController extends ApiController
{
    // PersonRepository savePersonLocation

    public function __construct(PersonRepository $people, Auth $auth, Response $response)
    {
        $this->people = $people;
        $this->auth = $auth;
        $this->response = $response;
    }

    public function create(Request $request) {
        $input = $request->input();
        $location = $this->people->savePersonLocation($input['location_geo'], $input['location_person']);

        // Return up to date Member
        return $this->response->item($location, new UserLocationTransformer, 'user_location');
    }
}
