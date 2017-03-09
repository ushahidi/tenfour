<?php

namespace RollCall\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Filesystem\FileNotFoundException;

class UseravatarController extends Controller
{
	/**
	* Show useravatar
	*
	* @param $filename
	* @return Response
	*/
	public function show(Request $request)
	{
		$filepath = $request->path();
		try {
    		$file = Storage::get($filepath);
    		$mimetype = Storage::mimetype($filepath);
    		return (new Response($file, 200))
    		  ->header('Content-Type', $mimetype);
		} catch (FileNotFoundException $e) {
            \Log::info('File not found', $e);
			abort(404);
		}
	}
}
