<?php

namespace TenFour\Http\Requests\Reply;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;

class GetReplyRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $check_in = App::make('TenFour\Contracts\Repositories\CheckInRepository')
                 ->find($this->route('checkin'));

        // Admin has full access
        if ($this->user() && $this->user()->isMember($check_in['organization_id'])) {
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
            //
        ];
    }

}
