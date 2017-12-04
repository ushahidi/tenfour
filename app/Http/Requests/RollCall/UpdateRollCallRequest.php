<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class UpdateRollCallRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // @todo should anyone really be able to update a rollcall?

        $rollCall = App::make('RollCall\Contracts\Repositories\RollCallRepository')
                 ->find($this->route('rollcall'));

        if ($this->user()->isAdmin($rollCall['organization_id'])) {
            return true;
        }

        if ($this->user()->role === 'author' &&
            $this->user()->organization_id === $this->input('organization_id')) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        return [
            'sent'   => 'integer',
            'status' => 'in:pending,received,expired,cancelled,failed',
            'recipients.*.id'   => 'exists:users,id'
        ];
    }
}
