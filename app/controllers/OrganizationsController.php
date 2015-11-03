<?php

class OrganizationsController extends ApiController {

    function __contruct()
    {
        $this->beforeFilter('auth.basic', ['on' => 'post']);
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$organizations = Organization::all();
        
        return Response::json([
            'data' => $organizations->toArray()
        ]);
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if (! Input::get('name') or ! Input::get('sub_domain'))
        {
            return $this->setStatusCode(422)
                        ->respondWithError('Parameters failed Validation');
        }

        Organization::create(Input::all());

        return $this->respondCreated('Organization successfully created');

        ]);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$organization = Organization::find($id);

        if (! $organization)
        {
            return $this->respondNotFound('Organization does not exist');
        }

        return $this->respond([
            'data' => $organization
        ]);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
