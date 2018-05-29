<?php
namespace TenFour\Repositories;

use TenFour\Models\UnverifiedAddress;
use TenFour\Contracts\Repositories\UnverifiedAddressRepository;
use DB;

class EloquentUnverifiedAddressRepository implements UnverifiedAddressRepository
{
    public function all()
    {
        return UnverifiedAddress::all()->toArray();
    }

    public function update(array $input, $id)
    {
		$address = UnverifiedAddress::findorFail($id);
        $address->update($input);

        return $address->toArray();
    }

    public function create(array $input)
    {
        $address = UnverifiedAddress::create($input);

        return $address->toArray();
    }

    public function find($id)
    {
        return UnverifiedAddress::findOrFail($id)->toArray();
    }

    public function delete($id)
    {
		$address = UnverifiedAddress::findOrFail($id);
		$address->delete();

        return $address->toArray();
    }

    public function getByAddress($address, $token = false, $code = false)
    {
        $results = [];

        $query = UnverifiedAddress::where([
            'address' => $address,
        ]);

        if ($token) {
            $query->where([
                'verification_token' => $token,
            ]);
        }

        if ($code) {
            $query->where([
                'code' => $code,
            ]);
        }

        $address = $query->get();

        if (!$address->isEmpty()) {
            $results = $address->first()->toArray();
        }

        return $results;
    }

    public function incrementCodeAttempts($address)
    {
        DB::table('unverified_addresses')->where('address', '=', $address)->increment('code_attempts');
    }
}
