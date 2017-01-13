<?php
namespace RollCall\Services;

use Illuminate\Support\Facades\Auth;
use RollCall\Contracts\Repositories\UserRepository;

class PasswordGrantVerifier
{
    /**
     * @var UsersRepository
     */
    protected $users;
    /**
     * @param UsersRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }
    /**
     * @param $username
     * @param $password
     *
     * @return bool|mixed
     */
    public function verify($username, $password)
    {
        $credentials = [
    	        'username' => $username,
                'password' => $password,
        ];

        if (Auth::once($credentials)) {
          return Auth::user()->id;
        }

        return false;
    }
}
