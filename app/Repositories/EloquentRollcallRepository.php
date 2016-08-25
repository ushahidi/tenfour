<?php
namespace RollCall\Repositories;

use RollCall\Models\Rollcall;
use RollCall\Contracts\Repositories\RollcallRepository;

class EloquentRollcallRepository implements RollcallRepository
{
    public function all()
    {
        $rollcalls = Rollcall::all();

        return $rollcalls->toArray();
    }

    public function find($id)
    {
        $rollcall = Rollcall::find($id);

        return $rollcall->toArray();
    }

    public function create(array $input)
    {
        $rollcall = Rollcall::create($input);

        return $rollcall->toArray();
    }

    public function update(array $input, $id)
    {
        $rollcall = Rollcall::findorFail($id);
        $rollcall->update($input);
        
        return $rollcall->toArray();
    }

    public function delete($id)
    {

    }
    
}
