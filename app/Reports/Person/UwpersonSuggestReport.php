<?php
/**
 * Search for Person records using user search terms
 */
namespace App\Reports\Person;

use App\Models\Person;

use App\Edw\PersonsDataSource;
use App\Updaters\EdwParser;
use App\Edw\EdwConnection;
use Illuminate\Support\Facades\Log;

class UwpersonSuggestReport
{

    /**
     * @var PersonsDataSource
     */
    protected $edw;
    /**
     * @var EdwParser
     */
    protected $parser;

    protected $report;
    protected $scope;
    protected $searchterm;

    public function __construct($searchterm, $scope = null)
    {
        $this->setSearchTerm($searchterm);
        $this->scope = $scope;
        $this->edw = new PersonsDataSource(new EdwConnection(config('database.connections.edw'))); 
        $this->parser = new EdwParser();
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

        Log::debug("This is a debug message.");
        Log::debug($this->searchterm);
        

        $users = $this->edw->getUsersByNetid($this->searchterm);


        Log::debug(sizeof($users));
        Log::debug("PersonKey");
        Log::debug(gettype($users));
        // Log::debug(gettype($users[0]));
        // Log::debug($users[0]);

        $users_collection = collect($users);

        return $users_collection->map(function ($item, $key) {
            return 
                (object) $item
            ;
        });

        // return $users->map(function ($item, $key) {
        //     return [
        //         'PersonKey' => $item->PersonKey,
        //         'LegalName' => $item->LegalName,
        //         'LegalFirstName' => $item->LegalFirstName,
        //         'LegalLastName' => $item->LegalLastName,
        //         'EmployeeID' => $item->EmployeeID,
        //         'RegID' => $item->RegID,
        //         'UWNetID' => $item->UWNetID,
        //         'StudentId' => $item->StudentId
        //     ];
        // });

        //return (object) $users;
        
    }

    // Search by personid, exact match
    public function findUserByPersonId()
    {
        if (!$this->searchterm) {
            return collect([]);
        }

        Log::debug("This is a debug message.");
        Log::debug($this->searchterm);
        

        $users = $this->edw->getUserByPersonId($this->searchterm);


        Log::debug(sizeof($users));
        Log::debug("PersonKey");
        Log::debug(gettype($users));
        // Log::debug(gettype($users[0]));
        // Log::debug($users[0]);

        // $users_collection = collect($users);

        // $results = $users_collection->map(function ($item, $key) {
        //     return 
        //         (object) $item
        //     ;
        // });

        return $users[0];

        // return $users->map(function ($item, $key) {
        //     return [
        //         'PersonKey' => $item->PersonKey,
        //         'LegalName' => $item->LegalName,
        //         'LegalFirstName' => $item->LegalFirstName,
        //         'LegalLastName' => $item->LegalLastName,
        //         'EmployeeID' => $item->EmployeeID,
        //         'RegID' => $item->RegID,
        //         'UWNetID' => $item->UWNetID,
        //         'StudentId' => $item->StudentId
        //     ];
        // });

        //return (object) $users;
        
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

