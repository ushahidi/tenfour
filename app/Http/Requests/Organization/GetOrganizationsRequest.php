<?php

namespace RollCall\Http\Requests\Organization;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetOrganizationsRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Anyone can query by subdomain or name
        if ($this->query('subdomain') || $this->query('name')) {
            return true;
        }

        // A user can list their own orgs;
        if ($this->user()) {
            // Repo auto filters by current user id

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
        return [];
    }
}
