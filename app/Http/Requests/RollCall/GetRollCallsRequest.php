<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class GetRollCallsRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // If filtering by organization check whether user is org owner/ org admin
        if (!$this->query('organization')) {
            $this->merge([
                'organization' => $this->user()->organization_id
            ]);
        }

        // If user is *not* an admin/owner, filter to just their rollcalls
        // @todo find a better home for this?
        if (!$this->user()->isAdmin($this->query('organization'))) {
            $this->merge([
                'recipient_id' => $this->user()->id
            ]);
        }

        if ($this->user()->isMember($this->query('organization'))) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        return [
            //
        ];
    }

}
