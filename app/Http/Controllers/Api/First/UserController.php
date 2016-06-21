<?php
namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Http\Requests\User\GetUserRequest;
use RollCall\Http\Requests\User\DeleteUserRequest;
use RollCall\Http\Requests\User\CreateUserRequest;
use RollCall\Http\Requests\User\UpdateUserRequest;
use RollCall\Http\Requests\User\GetUsersRequest;

class UserController extends ApiController
{
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Get all users
     *
     * @param Request $request
     * @return Response
     */
    public function all(GetUsersRequest $request)
    {
        $users = $this->users->all();
        return $users;
    }

    /**
     * Create a user
     *
     * @param Request $request
     * @return Response
     */
    public function create(CreateUserRequest $request)
    {
        $user = $this->users->create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        return $user;
    }

    /**
     * Get a single user 
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetUserRequest $request, $id)
    {
        $user = $this->users->find($id);
        return $user;
    }

    /**
     *
     * @param Request $request
     * @param int $id
     * 
     * @return Response
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->users->update($request->all(), $id);
        return $user;
    }

    /**
     * Delete a user
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function delete(DeleteUserRequest $request, $id)
    {
        $user = $this->users->delete($id);
        return $user;
    }
}
