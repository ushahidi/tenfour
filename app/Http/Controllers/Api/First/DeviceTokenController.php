<?php

namespace TenFour\Http\Controllers\Api\First;

use TenFour\Models\User;
use TenFour\Models\DeviceToken;
use TenFour\Http\Requests\Person\DeviceTokenRequest;
use Dingo\Api\Auth\Auth;
use TenFour\Http\Response;

/**
 * @Resource("Tokens", uri="/api/v1/organizations/{org_id}/people/{person_id}/tokens")
 */

class DeviceTokenController extends ApiController
{
    public function __construct()
    {
    }

    /**
     * Register a new device token
     *
     * @Post("/")
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id")
     * })
     *
     * @Versions({"v1"})
     * @Request({
     *       "token": "token123123123123op1kpo2k"
     *  }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={})
     *
     * @param Request $request
     * @return Response
     */
    public function store(DeviceTokenRequest $request, $organization_id, $person_id)
    {
        $person = User::findOrFail($person_id);

        if (DeviceToken::where('token', $request['token'])->count()) {
            return response(200);
        }

        $comment = $person->deviceTokens()->create([
            'token' => $request['token'],
        ]);

        return response(200);
    }

    /**
     * Delete a device token
     *
     * @Post("/{token}")
     * @Parameters({
     *   @Parameter("org_id", type="number", required=true, description="Organization id"),
     *   @Parameter("person_id", type="number", required=true, description="Person id"),
     *   @Parameter("token",required=true, description="The device token to delete")
     * })
     *
     * @Versions({"v1"})
     * @Request({}, headers={"Authorization": "Bearer token"})
     * @Response(200, body={})
     *
     * @param Request $request
     * @return Response
     */
    public function delete(DeviceTokenRequest $request, $organization_id, $person_id, $token)
    {
        DeviceToken::where('token', $token)->where('user_id', $person_id)->delete();

        return response(200);
    }
}
