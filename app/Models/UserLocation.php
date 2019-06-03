<?php

namespace TenFour\Models;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Intervention\Image\Point;
use Illuminate\Support\Facades\Log;

class UserLocation extends Model
{
    use SpatialTrait;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users_location';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['location_text', 'location_geo', 'user_id'];

    protected $spatialFields = [
        'location_geo' => 'json'
    ];


    /**
     *
     * Replies belong to a contact
     */
    public function user()
    {
        return $this->belongsTo('TenFour\Models\User');
    }

    public function setGeoLocation($location_geo, $location_text) {
        if ($location_geo) {
            $this->setGeoLocationFromGeoJSON($location_geo);
        } else if ($location_text) {
            $this->setGeoLocationFromAddress($location_text);
        }
    }
    // given a json geo feature from the frontend, return a valid Geometry
    private function setGeoLocationFromGeoJSON($input) {
        // example input '{"type":"Point","coordinates":[3.4,1.2]}'
        return Geometry::fromJson($input);
    }

    // given a location string (address, country-city)
    // find the correct shape or lat lon with reverse geolocation
    // and set it as the correct geo shape 
    private function setGeoLocationFromAddress($address) {
        $apiKey = config('services.tenfour.maps_api_key');
        $maps = "https://maps.googleapis.com/maps/api/geocode/json";
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request(
                "GET",
                $maps, 
                [
                    GuzzleHttp\RequestOptions::SYNCHRONOUS => true,
                    'query' => [
                        'address' => $address, 
                        'key' => $apiKey
                    ],
                ]
            );
            $content = $response->getBody()->getContents();
            if (isset($content['results']) && isset($content['results']['geometry'])) {
                // assumes that if we receive an address we will treat is as coordinates 
                // instead of an area.
                $location = $content['results']['geometry']['location'];
                $point = new Point($location['lon'], $location['lat']);
                $this->location_geo = $point;
            }
        } catch (GuzzleException $e) {
            Log::warning($e->getMessage());
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
        }
    }
}
