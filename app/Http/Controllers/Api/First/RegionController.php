<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Http\Requests\Region\GetSupportedRegionsRequest;
use TenFour\Http\Transformers\RegionTransformer;
use TenFour\Http\Response;
use libphonenumber\PhoneNumberUtil;

/**
 * @Resource("Supported regions", uri="/api/v1/regions")
 */
class RegionController extends ApiController
{
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function all(GetSupportedRegionsRequest $request)
    {
        $util = PhoneNumberUtil::getInstance();

        // All configured regions are available for all orgs for now.
        $providers =  config('tenfour.messaging.sms_providers');

        unset($providers['default']);

        // Extract ISO 3166-1 Alpha-2 codes
        $supported_codes = array_keys($providers);

        $regions = [];

        foreach($supported_codes as $code)
        {
            $country_code = $util->getCountryCodeForRegion($code);

            array_push($regions, [
                'country_code' => $country_code,
                'code'         => $code
            ]);
        }

        return $this->response->collection($regions, new RegionTransformer, 'regions');
    }
}
