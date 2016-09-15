<?php
namespace RollCall\Http\Controllers\Api\First;

use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Http\Requests\User\GetUserRequest;
use RollCall\Http\Requests\User\DeleteUserRequest;
use RollCall\Http\Requests\User\CreateUserRequest;
use RollCall\Http\Requests\User\UpdateUserRequest;
use RollCall\Http\Requests\User\GetUsersRequest;

use RollCall\Http\Transformers\UserTransformer;
use RollCall\Http\Response;

class UserController extends ApiController
{
    public function __construct(UserRepository $users, Response $response)
    {
        $this->users = $users;
        $this->response = $response;
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
        return $this->response->collection($users, new UserTransformer, 'users');
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

        return $this->response->item($user, new UserTransformer, 'user');
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

        return $this->response->item($user, new UserTransformer, 'user');
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
        
        return $this->response->item($user, new UserTransformer, 'user');
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
        
        return $this->response->item($user, new UserTransformer, 'user');
    }
}
