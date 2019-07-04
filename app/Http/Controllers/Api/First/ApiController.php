<?php
namespace TenFour\Http\Controllers\Api\First;

use Dingo\Api\Exception\ValidationHttpException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
	use DispatchesJobs, ValidatesRequests;

	public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
	{
		$validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

		if ($validator->fails()) {
			throw new ValidationHttpException($validator->errors());
		}
	}
}
