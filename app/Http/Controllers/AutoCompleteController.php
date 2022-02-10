<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;

class AutoCompleteController extends Controller
{
     public function index()
    {
        return view('search');
    }
 
    public function search(Request $request)
    {
          $search = $request->get('q');
      
          $result = Person::where('firstname', 'LIKE', '%'. $search. '%')->get();
 
          return response()->json($result);
            
    } 

    //
}
