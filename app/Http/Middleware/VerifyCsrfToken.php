<?php

namespace RollCall\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Support\Str;

class VerifyCsrfToken extends BaseVerifier
{
	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array
	 */
	protected $except = [
		//
	];

	protected function shouldPassThrough($request)
	{
		if (Str::is('application/vnd.rollcall.v*', $request->header('accept'))) {
			return true;
		}

		return parent::shouldPassThrough($request);
	}
}
