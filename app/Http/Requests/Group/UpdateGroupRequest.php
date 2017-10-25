<?php

namespace RollCall\Http\Requests\Group;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class UpdateGroupRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $group = App::make('RollCall\Contracts\Repositories\GroupRepository')
                 ->find($this->route('organization'), $this->route('group'));

        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'members.*.id'   => 'exists:users,id'
        ];
    }
}
