<?php

namespace TenFour\Http\Requests\CheckIn;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;

class UpdateCheckInRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // @todo should anyone really be able to update a check-in?

        $check_in = App::make('TenFour\Contracts\Repositories\CheckInRepository')
                 ->find($this->route('checkin'));

        if ($this->user()->isAdmin($check_in['organization_id'])) {
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
