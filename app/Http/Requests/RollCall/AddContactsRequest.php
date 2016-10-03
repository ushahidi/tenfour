<?php

namespace RollCall\Http\Requests\RollCall;

use App;
use Validator;

class AddContactsRequest extends GetRollCallRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = 'required|integer|org_member:'.$this->route('rollcall');

        Validator::extend('org_member', function($attr, $value, $params) {
            $rollcall_id = $params[0];
            $contact_id = $value;

            $rollcall = App::make('RollCall\Contracts\Repositories\RollCallRepository')
                      ->find($rollcall_id);

            $user = App::make('RollCall\Contracts\Repositories\ContactRepository')
                  ->getUser($contact_id);

            return App::make('RollCall\Contracts\Repositories\OrganizationRepository')
                       ->isMember($user['id'], $rollcall['organization_id']);
        });

        if (is_array(head($this->all()))) {
            $contacts = [];

            foreach($this->input() as $key => $val)
            {
                $contacts[$key.'.id'] = $rules;
            }

            return $contacts;
        }

        // ...else validate single contact
        return [
            'id' => $rules
        ];
    }
}
