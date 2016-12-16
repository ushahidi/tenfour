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
use Dingo\Api\Auth\Auth;

/**
 * @Resource("Users", uri="/api/v1/users")
 */
class UserController extends ApiController
{
    public function __construct(UserRepository $users, Response $response, Auth $auth)
    {
        $this->users = $users;
        $this->response = $response;
        $this->auth = $auth;
    }

    /**
     * Show all users
     *
     * Get a JSON representation of all the registered users.
     *
     * @Get("/")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
            "users": {{
                "name": "Robbie",
                "email": "robbie@ushahidi.com"
            }}
        })
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
     * @Post("/")
     * @Versions({"v1"})
     * @Request({
                "id": 3,
                "name": "Testing Testing",
                "email": "test@ushahidi.com",
                "password": "newpassword",
                "password_confirm": "newpassword"
            }, headers={"Authorization": "Bearer token"})
     * @Response(201)
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
            'person_type' => $request->input('person_type'),
        ]);

        return $this->response->item($user, new UserTransformer, 'user');
    }

    /**
     * Get a single user
     *
     * @Get("/{id}")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(200, body={
                "user": {
                    "id": 3,
                    "name": "Testing Testing",
                    "email": "test@ushahidi.com",
                    "username": "ushahidi",
                    "created_at": "2016-03-30 16:11:36",
                    "updated_at": "2016-03-30 16:11:36"
                }
            })
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function find(GetUserRequest $request, $id)
    {
        if ($id === 'me') {
            $id = $this->auth->user()['id'];
        }

        $user = $this->users->find($id);

        return $this->response->item($user, new UserTransformer, 'user');
    }

    /**
     * Update a user
     *
     * @Put("/{id}")
     * @Versions({"v1"})
     * @Request({
                "name": "Testing Testing",
                "email": "test@ushahidi.com",
                "password": "newpassword",
                "password_confirm": "newpassword"
            }, headers={"Authorization": "Bearer token"})
     * @Response(200, body={
                "user": {
                    "id": 3,
                    "name": "Testing Testing",
                    "email": "test@ushahidi.com",
                    "username": "ushahidi",
                    "created_at": "2016-03-30 16:11:36",
                    "updated_at": "2016-03-30 16:11:36"
                }
            })
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
     * @Delete("/{id}")
     * @Versions({"v1"})
     * @Request(headers={"Authorization": "Bearer token"})
     * @Response(201)
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
