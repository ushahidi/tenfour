<?php
namespace RollCall\Services;

use Illuminate\Support\Facades\Auth;
use RollCall\Contracts\Repositories\PersonRepository;

class PasswordGrantVerifier
{
    /**
     * @var PersonRepository
     */
    protected $people;
    /**
     * @param PersonRepository $people
     */
    public function __construct(PersonRepository $people)
    {
        $this->people = $people;
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
