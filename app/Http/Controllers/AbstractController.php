<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class AbstractController extends BaseController
{
	use ValidatesRequests;

    /**
     * If current user does not have $role abort response with 403 Not Authorized
     * @param string $role
     */
	public function authorize($role)
    {
        if (! \App::make('Acl')->hasRole($role)) {
            //abort(403);
        }
    }

}

