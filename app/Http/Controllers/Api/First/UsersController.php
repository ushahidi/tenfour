<?php
namespace RollCall\Http\Controllers\Api\First;

use RollCall\Entities\Models\User;

class UsersController extends ApiController
{
	/**
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function create()
	{
		// Validates input
		$input = $request->all();


	}

	/**
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show()
	{
		return User::all();
	}
}
