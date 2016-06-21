<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Contracts\Repositories\OrganizationRepository;
use DB;

class EloquentOrganizationRepository implements OrganizationRepository
{
    public function all()
    {
        return Organization::all();
    }

    public function update(array $input, $id)
    {
        $organization = Organization::findorFail($id);
        $organization->update($input);
        return $organization;
    }

    public function create(array $input)
    {
        return Organization::create($input);
    }

    public function find($id)
    {
        return Organization::find($id);
    }

    public function delete($id)
    {
        $organization = Organization::findorFail($id);
        $organization->delete();
        return $organization;
    }

    public function adminExists($organization_id, $user_id)
    {
        return (bool) DB::table('organization_admins')
            ->where('organization_id', $organization_id)
            ->where('user_id', $user_id)->count();
    }
}
