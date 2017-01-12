<?php

namespace RollCall\Http\Requests\Contact;

use RollCall\Http\Requests\Request;
use RollCall\Traits\UserAccess;
use App;

class UpdateContactRequest extends CreateContactRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin has access
        if ($this->isAdmin()) {
            return true;
        }

        $contact = App::make('RollCall\Contracts\Repositories\ContactRepository')
                 ->find($this->route('contact'));

        // A user can edit their own contact details
        if ($this->isSelf($contact['user_id'])) {
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
        $rules = parent::rules();

        $rules['contact'][1]->ignore($this->request->get('id'), 'id');

        return $rules;
    }
}
