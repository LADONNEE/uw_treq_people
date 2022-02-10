<?php
/**
 * Search for Person records using user search terms
 */
namespace App\Reports\Person;

use App\Models\Person;

class PersonSuggestReport
{
    protected $report;
    protected $scope;
    protected $searchterm;

    public function __construct($searchterm, $scope = null)
    {
        $this->setSearchTerm($searchterm);
        $this->scope = $scope;
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

