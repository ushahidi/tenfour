<?php
namespace TenFour\Repositories;

use TenFour\Models\Organization;
use TenFour\Models\Group;
use TenFour\Contracts\Repositories\GroupRepository;
use TenFour\Http\Transformers\UserTransformer;

use DB;
use Validator;

class EloquentGroupRepository implements GroupRepository
{
    public function __construct()
    {
    }

    // OrgCrudRepository
    public function all($organization_id, $offset = 0, $limit = 0, $filter = null)
    {
        $query = Group::where('organization_id', '=', $organization_id)
                    ->with('members.contacts')
                    ->orderBy('name', 'asc');

        if ($filter) {
            $query = $query->whereRaw( "LOWER(`name`) like ?", array( '%'.strtolower($filter).'%' ) );
        }

        if ($limit > 0) {
            $query
              ->offset($offset)
              ->limit($limit);
        }

        $groups = $query->get()->toArray();

        foreach ($groups as &$group)
        {
            foreach ($group['members'] as &$member)
            {
                  $member = (new UserTransformer)->transform($member);
            }
        }

        return $groups;
    }

    // OrgCrudRepository
    public function create($organization_id, array $input)
    {
        if (isset($input['name'])) {
            $name = $input['name'];
        }

        if (isset($input['description'])) {
            $description = $input['description'];
        }

        if (isset($input['_input_image'])) {
            $file = $input['_input_image'];
            $input['profile_picture'] = $this->storageService->storeBase64File($file, uniqid(), 'useravatar');
            unset($input['_input_image']);
        }

        $group = new Group;
        $group->fill($input, $name, $description);

        $group->organization_id = $organization_id;
        $group->save();

        //Notification::send($this->getAdmins($organization['id']),
            //new PersonJoinedOrganization($user));

        //return $group->toArray();

        $memberIds = collect($input['members'])->pluck('id')->all();
        $group->members()->sync($memberIds);

        return $group->fresh()
            ->toArray();
        /*
        //create a group with members
        $group = Group::create(['organization_id' => $organization_id, 'name' => $input['name']]);

        if (isset($input['_input_image'])) {
            $file = $input['_input_image'];
            $input['profile_picture'] = $this->storageService->storeBase64File($file, uniqid(), 'useravatar');
            unset($input['_input_image']);
        }

        $memberIds = collect($input['members'])->pluck('id')->all();
        $group->members()->sync($memberIds);

        return $group->fresh()
            ->toArray();
        */
    }

    // OrgCrudRepository
    public function update($organization_id, array $input, $id)
    {
        $input = array_only($input, ['members', 'name', 'description']);

        $group = Group::where('id', $id)
            ->where('organization_id', $organization_id)
            ->firstOrFail();

        if (isset($input['name'])) {
            $group->name = $input['name'];
        }

        if (isset($input['description'])) {
            $group->description = $input['description'];
        }
        /* Updating user-avatar */
        if (isset($input['_input_image']))
        {
            $file = $input['_input_image'];
            $input['profile_picture'] = $this->storageService->storeBase64File($file, uniqid($user_id), 'useravatar');
            unset($input['_input_image']);
        }
        /* end of user-avatar-code */

        $group->save();

        if (isset($input['members'])) {
            $memberIds = collect($input['members'])->pluck('id')->all();
            $group->members()->sync($memberIds);
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
            ->with('members.contacts')
            ->firstOrFail()
            ->toArray();

        foreach ($group['members'] as &$member)
        {
              $member = (new UserTransformer)->transform($member);
        }

        return $group;
    }

    public function findBySource($organization_id, $source, $source_id)
    {
        return Group::where('source', '=', $source)
            ->where('source_id', '=', $source_id)
            ->where('organization_id', '=', $organization_id)
            ->first();
    }
}
