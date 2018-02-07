<?php
namespace TenFour\Repositories;

use TenFour\Models\User;
use TenFour\Models\Organization;
use TenFour\Models\Contact;
use TenFour\Models\ContactFiles;
use League\Csv\Reader;
use TenFour\Contracts\Repositories\ContactFilesRepository;

class EloquentContactFilesRepository implements ContactFilesRepository
{
    public function create(array $input)
    {
        $file = ContactFiles::create($input);

        return $file->toArray();
    }

    public function update(array $input, $id)
    {
        $file = ContactFiles::findorFail($id);
        $file->update($input);

        return $file->toArray();
    }

    public function delete($id)
    {
        $file = ContactFiles::findorFail($id);
        $file->delete();

        return $file->toArray();
    }

    public function all()
    {
        //
    }

    public function find($id)
    {
        return ContactFiles::findorFail($id)->toArray();
    }

}
