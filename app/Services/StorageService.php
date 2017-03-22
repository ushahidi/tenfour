<?php

namespace RollCall\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    public function __construct()
    {
    }

    public function storeBase64File($file, $id, $prefix = 'avatar')
    {
        $filename = $id;
        list($extension, $file) = explode(';', $file);
        list(, $extension) = explode('/', $extension);
        list(, $file) = explode(',', $file);
        $file = base64_decode($file);
        $path = $prefix . '/'.$filename . '.' . $extension;
        Storage::put($path, $file, 'public');

        return Storage::url($path);
    }
}
