<?php

namespace TenFour\Http\Requests;

use Dingo\Api\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
	/**
	 * @return array
	 */
	public function rules()
	{
        return [
            'address' => 'email',
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
