<?php

namespace RollCall\Http\Controllers;

use RollCall\Models\Organization;
use RollCall\Http\Controllers\Controller;

class HealthController extends Controller
{
    /**
    * Perform a shallow health check.
    *
    * @return JSON
    */
    protected function shallow()
    {
        return response()->json(["health" => "OK"]);
    }

    /**
    * Perform a deep health check.
    *
    * @return JSON
    */
    protected function deep()
    {
        $health = $database = "OK";

        try {
        		Organization::firstOrFail();
        } catch (\Illuminate\Database\QueryException $e) {
          	$database = $e->getMessage();
        		$health = "FAIL";
        }

        if ($health == "OK") {
            $status = 200;
        } else {
            $status = 503;
        }
        
        return response()->json([
        		"health" => $health,
        		"database" => $database
        ], $status);
    }
}
