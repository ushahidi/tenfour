<?php

namespace RollCall\Http\Requests\FileUpload;

use Dingo\Api\Http\FormRequest;
use RollCall\Traits\UserAccess;

class CreateFileUploadRequest extends UpdateFileUploadRequest
{
    use UserAccess;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Maximum size allowed in kB
        $max_allowed_size = '2048';

        return [
            'file' => 'required|file|mimetypes:text/plain|max:'.$max_allowed_size,
        ];
    }
}
