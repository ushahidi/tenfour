<?php

namespace RollCall\Http\Requests\RollCall;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;
use App;

class GetRollCallRequest extends FormRequest
{
    use UserAccess;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $rollCall = App::make('RollCall\Contracts\Repositories\RollCallRepository')
                 ->find($this->route('rollcall'));

        // If user is a receipient or author, they can view the rollcall
        // @todo this would be much easier with the full RollCall object
        $userId = $this->auth->user()['id'];

        if ($rollCall['user_id'] === $userId) {
            return true;
        }

        $matchedRecipient = array_filter($rollCall['recipients'], function ($recipient) use ($userId) {
            return $recipient['id'] === $userId;
        });
        if (count($matchedRecipient)) {
            return true;
        }

        if ($this->user()->isAdmin($rollCall['organization_id'])) {
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
