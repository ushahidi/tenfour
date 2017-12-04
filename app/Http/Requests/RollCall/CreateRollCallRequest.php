<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class CreateRollCallRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->user()->isAdmin($this->input('organization_id'))) {
            return true;
        }

        if ($this->user()->role === 'author' &&
            $this->user()->organization_id === $this->input('organization_id')) {
            return true;
        }

        // Check if user is sending themselves a roll call
        if (count($this->input('recipients')) == 1 &&
            $this->isSelf($this->input('recipients.0.id'))) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message'         => 'required',
            'organization_id' => 'required|integer|exists:organizations,id',
            'recipients'      => 'required',
            'recipients.*.id' => 'required|exists:users,id'
        ];
    }

}
