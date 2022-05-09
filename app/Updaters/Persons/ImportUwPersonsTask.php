<?php

namespace App\Updaters\Persons;

use App\Edw\PersonsDataSource;
use App\Models\Person;
use App\Updaters\EdwParser;
use Illuminate\Support\Facades\DB;

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
                'uwnetid' => $data['uwnetid'] /*,
                'person_id' => $data['person_id'],                
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'studentno' => $data['studentno'],
                'employeeid' => $data['employeeid'],
                'email' => $data['email']*/
            ]);
            $person->fill($data);
            $person->updating = 0;
            $person->save();
        }

        
    }

    public function parseRow($row)
    {
        $out = [];
        foreach ($row as $index => $value) {
            $out[$index] = $this->parser->string($value);
        }
        /*$out['EffectiveDate'] = $this->parser->dateYmd($row['EffectiveDate']);
        $out['TotalPeriodBeginDate'] = $this->parser->dateYmd($row['TotalPeriodBeginDate']);
        $out['TotalPeriodEndDate'] = $this->parser->dateYmd($row['TotalPeriodEndDate']);*/
        $out['studentno'] = $this->parser->integer($row['studentno']);
        $out['employeeid'] = $this->parser->integer($row['employeeid']);

        /*if ($out['FoodApprovalInd'] === null) {
            $out['FoodApprovalInd'] = 0;
        }*/
        return $out;
    }

    
}
