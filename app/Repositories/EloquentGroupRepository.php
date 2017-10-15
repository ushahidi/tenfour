<?php
namespace RollCall\Repositories;

use RollCall\Models\Organization;
use RollCall\Models\Group;
use RollCall\Contracts\Repositories\GroupRepository;
use RollCall\Http\Transformers\OrganizationTransformer;
use DB;
use Validator;

class EloquentGroupRepository implements GroupRepository
{
    public function __construct()
    {
    }

    // OrgCrudRepository
    public function all($organization_id, $offset = 0, $limit = 0)
    {
        $query = Group::where('organization_id', '=', $organization_id)
                        ->orderBy('name', 'asc');

        if ($limit > 0) {
            $query
              ->offset($offset)
              ->limit($limit);
        }

        $groups = $query->get();

        return $groups->toArray();

    }

    // OrgCrudRepository
    public function create($organization_id, array $input)
    {
        //create a group with members
        $group = Group::create(['organization_id' => $organization_id, 'name' => $input['name']]);

        $memberIds = collect($input['members'])->pluck('id')->all();
        $group->members()->sync($memberIds);
  
        return $group->fresh()
            ->toArray();
    }

    // OrgCrudRepository
    public function update($organization_id, array $input, $id)
    {
        $input = array_only($input, ['members']);

        $group = Group::where('id', $id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        $group->save();

        if (isset($input['members'])) {
            $memberIds = collect($input['members'])->pluck('id')->all();
            $group->members()->syncWithoutDetaching($memberIds);
        }

        return $group->fresh()->toArray();
    }

    // OrgCrudRepository
    public function delete($organization_id, $group_id)
    {
        $group = Group::where('id', $group_id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        $group->delete();

        return $group->toArray();
    }

    // OrgCrudRepository
    public function find($organization_id, $id)
    {
        $group = Group::where('id', $id)
            ->firstOrFail();

        return $group->toArray();
    }

}
