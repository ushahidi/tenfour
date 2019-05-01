<?php

namespace TenFour\Http\Requests\CheckIn;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;
use TenFour\Models\CheckIn;

class DeleteScheduledCheckinRequest extends FormRequest
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
        $check_in = CheckIn::query()->where('scheduled_checkin_id', '=', $this->route('id'))->first();
        if ($this->user()->isAdmin($check_in->organization_id)) {
            return true;
        }

        if ($this->user()->role === 'author' &&
            $this->user()->organization_id === $this->input('organization')) {
            return true;
        }
        return false;
    }

    public function rules()
    {
        return [
        ];
    }
}
