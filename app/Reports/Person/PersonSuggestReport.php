<?php
/**
 * Search for Person records using user search terms
 */
namespace App\Reports\Person;

use App\Edw\PersonsDataSource;
use App\Edw\EdwConnection;
use App\Models\Person;
use Carbon\Carbon;
use App\Updaters\EdwParser;

class PersonSuggestReport
{
    protected $report;
    protected $scope;
    protected $searchterm;
    /**
     * @var EdwParser
     */
    protected $parser;

    public function __construct($searchterm, $scope = null)
    {
        $this->setSearchTerm($searchterm);
        $this->scope = $scope;
        $this->parser = new EdwParser;
    }

    public function prefetch()
    {
        $query = Person::select('uw_persons.*')
            ->orderBy('uw_persons.lastname')
            ->orderBy('uw_persons.firstname');
        $this->addScope($query);
        return $query->get();
    }

    public function search()
    {
        if (!$this->searchterm) {
            return collect([]);
        }
        $query = Person::select('uw_persons.*')
            ->orderBy('uw_persons.lastname')
            ->orderBy('uw_persons.firstname');
        $this->addScope($query);
        $this->addSearchTerms($query);
        return $query->get();
    }

    public function searchUWOne(int $personId)
    {
        
        $edw = new EdwConnection(config('database.connections.edw'));
        
        $wherestatement = " p.PersonKey LIKE '" . $personId . "' ";

        $sql = sqlInclude(__DIR__ .'/personsuwall.sql', [
            '__WHERESTATEMENT__' => $wherestatement,
            '__MATCH__' => "'%'",
            '__VALIDITY__' => "'" . Carbon::now()->format('Y-m-d') . "'" //->format('Y-m-d')
        ]);

        $row = $edw->fetchAssoc($sql);

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

        //return $results;
    }

    public function parseRow($row)
    {
        $out = [];
        /*foreach ($row as $index => $value) {
            $out[$index] = $this->parser->string($value);
        }*/

        $out['uwnetid'] = $this->parser->string($row['UWNetID']);
        $out['person_id'] = $this->parser->string($row['PersonKey']);
        $out['firstname'] = $this->parser->string($row['LegalFirstName']);
        $out['lastname'] = $this->parser->string($row['LegalLastName']);
        

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

    public function searchUWAll()
    {
        if (!$this->searchterm) {
            return collect([]);
        }
        /*$query = Person::select('uw_persons.*')
            ->orderBy('uw_persons.lastname')
            ->orderBy('uw_persons.firstname');*/

        //Prepare search term regex
        //$searchtermregex = "|";
        $wherestatement = "";
        $terms = explode(' ', $this->searchterm);
        foreach ($terms as $term) {
            
            $wherestatement = $wherestatement . " OR p.UWNetID LIKE '%" . $term . "%' OR p.DisplayName LIKE '%" . $term . "%'";

        }
        
        $wherestatement = ltrim($wherestatement, ' OR');

        //$wherestatement = 'nbedani';

        //execute query with search term regex
        //$results = PersonsDataSource.getPositionsUWAll($searchtermregex);
        

        $edw = new EdwConnection(config('database.connections.edw'));
        

        //$results = $pds.getPositionsUWAll($searchtermregex);
        
        $sql = sqlInclude(__DIR__ .'/personsuwall.sql', [
            '__WHERESTATEMENT__' => $wherestatement,
            '__MATCH__' => "'%'",
            '__VALIDITY__' => "'" . Carbon::now()->format('Y-m-d') . "'" //->format('Y-m-d')
        ]);

        return $edw->fetchAssoc($sql);


        


        //return $results;
    }

    public function addScope($query)
    {
        $scope = $this->scope;
        if (!$scope || $scope === 'all' ) {
            return;
        }
        /*if ($scope === 'coe') {
            $query->join('p_coe_authorized', 'uw_persons.person_id', '=', 'p_coe_authorized.person_id');
        }
        if ($scope === 'coe-uwnetid') {
            $query->join('p_coe_authorized', 'uw_persons.person_id', '=', 'p_coe_authorized.person_id');
            $query->where(function ($q) {
                $q->where('uw_persons.uwnetid', '<>', '')
                 ->whereNotNull('uw_persons.uwnetid');
            });
        }*/
        if ($scope === 'employee') {
            $query->where(function ($q) {
                $q->where('uw_persons.employeeid', '<>', '')
                    ->whereNotNull('uw_persons.employeeid');
            });
        }
        if ($scope === 'student') {
            $query->where(function ($q) {
                $q->where('uw_persons.studentno', '<>', '')
                    ->whereNotNull('uw_persons.studentno');
            });
        }
        if ($scope === 'uwnetid') {
            $query->where(function ($q) {
                $q->where('uw_persons.uwnetid', '<>', '')
                    ->whereNotNull('uw_persons.uwnetid');
            });
        }
    }

    public function addSearchTerms($query)
    {
        $terms = explode(' ', $this->searchterm);
        foreach ($terms as $term) {
            $query->where(function($query) use($term) {
                $query->where('uwnetid', 'LIKE', $term.'%')
                    ->orWhere('firstname', 'LIKE', $term.'%')
                    ->orWhere('lastname', 'LIKE', $term.'%');
            });
        }
    }

    public function setSearchTerm($searchterm)
    {
        $this->searchterm = preg_replace('/\s+/', ' ', strtolower(trim($searchterm)));
    }

}

