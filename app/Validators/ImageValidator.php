<?php

namespace User\Validators;

use Intervention\Image\ImageManagerStatic as Image;

class ImageValidator
{
    public function __construct()
    {

    }

    public function validateProfilePictureUpload($attr, $value, $params)
    {
        $image = Image::make($value);

        $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'];

        return $image->filesize() <= 2048 && in_array($image->mime(), $allowed_mime_types);
    }
}
