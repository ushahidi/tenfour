<?php
namespace RollCall\Repositories;

use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Models\Contact;
use RollCall\Models\ContactFiles;
use League\Csv\Reader;
use RollCall\Contracts\Repositories\ContactFilesRepository;

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
