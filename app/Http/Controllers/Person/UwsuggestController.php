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

class UwsuggestController extends AbstractController
{

    public function prefetch()
    {
        $report = new UwpersonSuggestReport(null, request('scope', 'coe')); 
        $results = $report->prefetch();
        return response()->json($this->prepare($results));
    }

    public function suggest()
    {
        $report = new UwpersonSuggestReport(request('q'), request('scope'));
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


                // 'person_id' => $item->PersonKey,
                // 'LegalName' => $item->LegalName,
                // 'LegalFirstName' => $item->LegalFirstName,
                // 'LegalLastName' => $item->LegalLastName,
                // 'EmployeeID' => $item->EmployeeID,
                // 'RegID' => $item->RegID,
                // 'UWNetID' => $item->UWNetID,
                // 'StudentId' => $item->StudentId

    public function prepare($results)
    {
        $out = [];
        foreach ($results as $person) {
            $out[] = [
                'id'   => $person->PersonKey,
                'name' => $person->LegalFirstName . ' ' . $person->LegalLastName . ' ('.$person->UWNetID.')',
            ];
        }
        return $out;
    }

}

