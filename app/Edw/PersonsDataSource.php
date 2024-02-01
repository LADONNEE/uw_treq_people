<?php
/**
 * @package app.treq.person
 */
/**
 * Retrieves person and position data from EDW
 */
namespace App\Edw;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PersonsDataSource
{
    /* @var $edw Connection */
    protected $edw;

    public function __construct(EdwConnection $edw)
    {
        $this->edw = $edw;
        //$this->edw = \App::make('edw');
    }

    /**
     * EDW data for current employee position records from Workday data
     * @return array
     */
    public function getCollegePositions()
    {
        $match = "'" . config('app.db_query_persons') . "'"; // "'%UWORG%'";
        $validity = "'" . Carbon::now()->addYears(-1)->format('Y-m-d') . "'"; //"'2022-01-01' give one year of persistence before stopping update" ; 
        $sql = sqlInclude(__DIR__ .'/Queries/sql/persons.sql', [
            '__MATCH__' => $match,
            '__VALIDITY__' => $validity //->format('Y-m-d')
        ]);
        return $this->edw->fetchAssoc($sql);
    }

    /**
     * EDW data for employee based on netid from Workday data
     * @return array
     */
    public function getUsersByNetid($usernetid)
    {
        $match = "'%" . $usernetid . "%'"; // "'%nbedani%'";
        
        $sql = sqlInclude(__DIR__ .'/Queries/sql/personsbynetid.sql', [
            '__MATCH__' => $match
        ]);

        Log::debug($sql);

        Log::info(print_r($this->edw->fetchAssoc($sql), true));
        

        return $this->edw->fetchAssoc($sql);
    }


    public function getUserByPersonId($person_id)
    {
        $match = "'" . $person_id . "'"; // "'123456'";
        
        $sql = sqlInclude(__DIR__ .'/Queries/sql/personbypersonid.sql', [
            '__MATCH__' => $match
        ]);

        Log::debug($sql);

        Log::info(print_r($this->edw->fetchAssoc($sql), true));
        

        return $this->edw->fetchAssoc($sql);
    }



}
