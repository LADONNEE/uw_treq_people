<?php

namespace App\Updaters\Persons;

use App\Edw\PersonsDataSource;
use App\Models\Person;
use App\Updaters\EdwParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Reports\Person\UwpersonSuggestReport;

/**
 * Query budget data from UW EDW connection and save records locally in uw_budgets_cache
 */
class ImportUwPersonsTask
{
    /**
     * @var PersonsDataSource
     */
    protected $edw;
    /**
     * @var EdwParser
     */
    protected $parser;

    public function __construct(PersonsDataSource $edw, EdwParser $parser)
    {
        $this->edw = $edw;
        $this->parser = $parser;
    }

    public function run()
    {
        $results = $this->edw->getCollegePositions();
        foreach ($results as $row) {
            $data = $this->parseRow($row);
            $person = Person::firstOrNew([
                //'uwnetid' => $data['UWNetID'],
                'person_id' => $data['person_id'] /*,                
                'firstname' => $data['LegalFirstName'],
                'lastname' => $data['LegalLastName'],
                'studentno' => $data['studentno'],
                'employeeid' => $data['employeeid'],
                'email' => $data['email']*/
            ]);
            $person->fill($data);
            //$person->updating = 0;
            $person->save();
        }

        
    }

    public function parseRow($row)
    {
        $out = [];
        /*foreach ($row as $index => $value) {
            $out[$index] = $this->parser->string($value);
        }*/

        $out['uwnetid'] = $this->parser->string($row['UWNetID']);
        $out['person_id'] = $this->parser->string($row['PersonKey']);
        $out['firstname'] = $this->parser->string($row['DisplayFirstName']);
        $out['lastname'] = $this->parser->string($row['DisplayLastName']);
        

        /*$out['EffectiveDate'] = $this->parser->dateYmd($row['EffectiveDate']);
        $out['TotalPeriodBeginDate'] = $this->parser->dateYmd($row['TotalPeriodBeginDate']);
        $out['TotalPeriodEndDate'] = $this->parser->dateYmd($row['TotalPeriodEndDate']);*/
        $out['studentno'] = $this->parser->integer($row['StudentId']);
        $out['employeeid'] = $this->parser->integer($row['EmployeeID']);

        /*if ($out['FoodApprovalInd'] === null) {
            $out['FoodApprovalInd'] = 0;
        }*/
        return $out;
    }

    public function importAdHocUser($person_id)
    {

            // Get user from EDW with $person_id

            $report = new UwpersonSuggestReport($person_id, 'uwnetid');
            $persondata = $report->findUserByPersonId();


            $data = $this->parseRow($persondata);
            $person = Person::firstOrNew([
                //'uwnetid' => $data['UWNetID'],
                'person_id' => $data['person_id'] /*,                 
                'firstname' => $data['LegalFirstName'],
                'lastname' => $data['LegalLastName'],
                'studentno' => $data['studentno'],
                'employeeid' => $data['employeeid'],
                'email' => $data['email']*/
            ]);

            Log::debug('Person data');
            Log::debug($data);

            $person->fill($data);
            //$person->updating = 0;
            $person->save();
            return $data['uwnetid'];
    }

        
    

    
}
