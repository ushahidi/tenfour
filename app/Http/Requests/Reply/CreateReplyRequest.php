<?php

namespace TenFour\Http\Requests\Reply;

use Dingo\Api\Http\FormRequest;
use App;

class CreateReplyRequest extends GetReplyRequest
{

    public function authorize()
    {
        $check_in = App::make('TenFour\Contracts\Repositories\CheckInRepository')
                 ->find($this->route('checkin'));
                 
        $token = $this->request->get('token');

        if ($token && !empty($token)) {
            return count(array_filter($check_in['recipients'], function ($recipient) use ($token) {
                return $recipient['pivot']['reply_token'] === $token;
            })) > 0;
        }

        return parent::authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'location_text'  => 'string|nullable',
          'answer'         => 'required_without:message|string|nullable',
          'message'        => 'required_without:answer|string|nullable',
        ];
    }
}
