<?php
/**
 * JSON responses for ajax tools
 */
namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractController;
use App\Models\Person;
use App\Reports\Person\PersonSuggestReport;
use App\Repositories\Person\PersonSearch;
use Illuminate\Http\Request;

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
        return response()->json($this->prepare($results));
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

}

