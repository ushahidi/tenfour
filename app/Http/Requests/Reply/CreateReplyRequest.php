<?php

namespace RollCall\Http\Requests\Reply;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class CreateReplyRequest extends GetReplyRequest
{
    use UserAccess;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'location_text'  => 'string',
          'answer'         => 'required_without: message|string',
          'message'        => 'required_without: answer|string',
        ];
    }
}
