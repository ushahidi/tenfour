<?php

namespace TenFour\Http\Requests\CheckIn;

use Dingo\Api\Http\FormRequest;
use TenFour\Traits\UserAccess;
use App;

class GetCheckInRequest extends FormRequest
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
    
        
        $token = $this->request->get('token');

        if ($token && !empty($token)) {
            return count(array_filter($check_in['recipients'], function ($recipient) use ($token) {
                return $recipient['pivot']['reply_token'] === $token;
            })) > 0;
        }

        if (!$this->auth->user()) {
            return false;
        }

        // If user is a receipient or author, they can view the check-in
        // @todo this would be much easier with the full check-in object
        $userId = $this->auth->user()['id'];

        if ($check_in['user_id'] === $userId) {
            return true;
        }

        $matchedRecipient = array_filter($check_in['recipients'], function ($recipient) use ($userId) {
            return $recipient['id'] === $userId;
        });

        if (count($matchedRecipient)) {
            return true;
        }

        if ($this->user()->isAdmin($check_in['organization_id'])) {
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
