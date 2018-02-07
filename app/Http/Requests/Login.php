<?php

namespace TenFour\Http\Requests;

class Login extends Request
{
	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			'email' => 'required|email',
			'password' => 'required',
		];
	}

	/**
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}
}
