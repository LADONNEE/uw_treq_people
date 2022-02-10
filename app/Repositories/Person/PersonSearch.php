<?php
/**
 * Search for Person records using user search terms
 */
namespace App\Repositories\Person;

use App\Models\Person;

class PersonSearch
{
    protected $report;
    protected $searchterm;
    protected $withAuthorizations = true;
    public $sql;

    public function __construct($searchterm)
    {
        $this->setSearchTerm($searchterm);
    }

    public function getSearchTerm()
    {
        return $this->searchterm;
    }

    public function persons()
    {
        if (!is_array($this->report)) {
            $this->load();
        }
        return $this->report;
    }

    public function load()
    {
        if (!$this->searchterm) {
            $this->report = [];
            return;
        }
        $terms = explode(' ', $this->searchterm);
        $query = Person::select('*');
        if ($this->withAuthorizations) {
            $query = $query->with('auths');
        }
        foreach ($terms as $term) {
            if (is_numeric($term)) {
                $term = (int) $term;
                $query->where(function($query) use($term) {
                    $query->where('systemkey', '=', $term)
                        ->orWhere('studentno', '=', $term)
                        ->orWhere('employeeid', '=', $term);
                });
            } else {
                $query->where(function($query) use($term) {
                    $query->where('uwnetid', 'LIKE', $term.'%')
                        ->orWhere('firstname', 'LIKE', $term.'%')
                        ->orWhere('lastname', 'LIKE', $term.'%')
                        ->orWhere('descriptor', 'LIKE', $term.'%');
                });
            }
        }
        $this->report = $query->orderBy('lastname')->orderBy('firstname')->get();
        $this->sql = $query->toSql();
    }

    public function setSearchTerm($searchterm)
    {
        $this->searchterm = preg_replace('/\s+/', ' ', strtolower(trim($searchterm)));
    }

    public function withAuthorizations($activate = true)
    {
        $this->withAuthorizations = (boolean) $activate;
    }

}

