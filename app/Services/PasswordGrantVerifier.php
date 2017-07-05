<?php
namespace RollCall\Services;

use Illuminate\Support\Facades\Auth;
use RollCall\Contracts\Repositories\PersonRepository;
use Illuminate\Http\Request;

class PasswordGrantVerifier
{
    /**
     * @var PersonRepository
     */
    protected $people;

    /**
     * @param PersonRepository $people
     * @param Request $request
     */
    public function __construct(PersonRepository $people, Request $request)
    {
        $this->people = $people;
        $this->request = $request;
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
            'username'     => $username,
            'password'     => $password,
            'subdomain'    => $this->request->subdomain,
        ];

        if (Auth::once($credentials)) {
          return Auth::user()->id;
        }

        return false;
    }
}
