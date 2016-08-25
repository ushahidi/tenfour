<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Contracts\Repositories\OrganizationRepository;
use DB;
use Dingo\Api\Auth\Auth;

class EloquentOrganizationRepository implements OrganizationRepository
{
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

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
        DB::transaction(function () use($input) {
            
            $organization = Organization::create($input);          

            //Flag the user creating the organization as the owner of the Org
            DB::table('organization_users')->insert([
                'organization_id' => $organization->id,
                'user_id' => $this->auth->user()->id, 
                'role' => 'owner'
            ]);
        });

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

    public function getUserById($organization_id, $user_id)
    {
        $user = DB::table('organization_users')->where('organization_id',$organization_id)->where('user_id', $user_id)->first();

        return $user;
    }

    public function addOrgAdmin($organization_id, $user_id)
    {
        $user = getUserById($organization_id, $user_id);

        if ($user === 'member')
        {
            return DB::table('organization_users')->update('role', 'admin')->where('user_id', $user_id);
        }

    }

    public function register($organization_id)
    {
        return DB::table('organization_users')->insert([
            'organization_id' => $organization_id,
            'user_id' => $this->auth->user()->id, 
            'role' => 'member'
        ]);   
    }

}
