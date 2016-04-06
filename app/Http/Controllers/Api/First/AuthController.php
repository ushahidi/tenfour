<?php
namespace RollCall\Http\Controllers\Api\First;

use Illuminate\Http\Request;
use LucaDegasperi\OAuth2Server\Authorizer;
use RollCall\Models\User;

class AuthController extends ApiController
{
	/**
	 * @type Authorizer
	 */
	protected $authorizer;

	/**
	 * @param Authorizer $authorizer
	 */
	public function __construct(Authorizer $authorizer)
	{
		$this->authorizer = $authorizer;
	}

	/**
	 * Workaround for oauth2-server-laravel only
	 * taking into account the request parameter and
	 * not the ones passed via application/json
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function token(Request $request)
	{
		// Validates input
		$input = $request->all();

		// Retrieves authorization token
		$request->request->replace($input);
		$this->authorizer->setRequest($request);

		// Sends user_id on the response so it can be used on subsequent requests.
		if ($input['grant_type'] === 'password') {
			$user = User::where('email', $input['username'])->first();

			return array_add($this->authorizer->issueAccessToken(), 'user_id', $user->id);
		}

		return $this->authorizer->issueAccessToken();
	}
}
