<?php

namespace RollCall\Http\Requests\Reply;

use Dingo\Api\Http\FormRequest;
use App;

class CreateReplyRequest extends GetReplyRequest
{

    public function authorize()
    {
        $rollCall = App::make('RollCall\Contracts\Repositories\RollCallRepository')
                 ->find($this->route('rollcall'));

        $token = $this->request->get('token');

        if ($token && !empty($token)) {
            return count(array_filter($rollCall['recipients'], function ($recipient) use ($token) {
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
