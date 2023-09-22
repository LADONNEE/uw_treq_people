<?php
/**
 * JSON responses for ajax tools
 */
namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractController;
use App\Http\Controllers\Log;
use App\Models\Person;
use App\Reports\Person\PersonSuggestReport;
use App\Repositories\Person\PersonSearch;
use Illuminate\Http\Request;
use App\Updaters\EdwParser;

class SuggestController extends AbstractController
{

    public function prefetch()
    {
        $report = new PersonSuggestReport(null, request('scope', 'coe'));
        $results = $report->prefetch();
        return response()->json($this->prepare($results));
    }

    public function suggest()
    {
        $report = new PersonSuggestReport(request('q'), request('scope'));
        $results = $report->search();

        // if no result found in treq db uw_persons, search in all edw organizations
        // if( request('q') != null && count($results) <= 0 ){
        //     $results = $report->searchUWAll();
        //     return response()->json($this->prepareUWAll($results));

        // }


        return response()->json($this->prepare($results));
    }

    public function saveuwperson()
    {

        //$report = new PersonSuggestReport('', '');
        
        //$report->searchUWOne(165736);

        return response()->json([
            'success' => true
        ]);
    }
    

    public function find()
    {
        $uwnetid = request('uwnetid');
        if ($uwnetid) {
            $person = Person::where('uwnetid', $uwnetid)->first();
        } else {
            $person = null;
        }
        if ($person) {
            return response()->json([
                'found' => true,
                'id' => $person->person_id,
                'firstname' => $person->firstname,
                'lastname' => $person->lastname,
                'uwnetid' => $person->uwnetid,
            ]);
        }
        return response()->json([
            'found' => false,
            'id' => null,
            'firstname' => null,
            'lastname' => null,
            'uwnetid' => null,
        ]);
    }

    public function prepare($results)
    {
        $out = [];
        foreach ($results as $person) {
            $out[] = [
                'id'   => $person->person_id,
                'name' => eNameNetID($person),
            ];
        }
        return $out;
    }

    public function prepareUWAll($results)
    {
        $parser = new EdwParser();

        $out = [];
        
        foreach ($results as $row) {
            $out[] = [
                'id'   => $parser->string($row['PersonKey']),
                'name' => $parser->string($row['DisplayName']) . ' (' . $parser->string($row['UWNetID']) . ')',
            ];
        }
        return $out;
    }

}

