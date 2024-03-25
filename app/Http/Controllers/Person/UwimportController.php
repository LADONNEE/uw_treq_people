<?php
/**
 * JSON responses for ajax tools
 */
namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractController;
use App\Models\Person;
use App\Reports\Person\UwpersonSuggestReport;
use App\Repositories\Person\PersonSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Edw\PersonsDataSource;
use App\Updaters\EdwParser;
use App\Updaters\Persons\ImportUwPersonsTask;
use App\Edw\EdwConnection;

class UwimportController extends AbstractController
{

    
    public function import()
    {
        Log::debug("Received post request");
        Log::debug(request('person_id'));
        //Populate persondata with values sent by treq

        
        $person_id = request('a');
        
        $edw = new PersonsDataSource(new EdwConnection(config('database.connections.edw'))); 
        $parser = new EdwParser();
        
        $result_uwnetid = (new ImportUwPersonsTask( $edw, $parser   ))->importAdHocUser($person_id);

        return response()->json($result_uwnetid);
    }

    


}

