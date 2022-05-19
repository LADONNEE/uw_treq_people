<?php
/**
 * @package edu.uw.environment.person
 */
/**
 * Retrieves person and position data from UW EDW ODS
 */
namespace App\Edw;

use Carbon\Carbon;

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
        $match = "'%UAA%'";
        $validity = "'2022-01-01'" ;  // Carbon::now()->addYears(1);
        $sql = sqlInclude(__DIR__ .'/Queries/sql/persons.sql', [
            '__MATCH__' => $match,
            '__VALIDITY__' => $validity //->format('Y-m-d')
        ]);
        return $this->edw->fetchAssoc($sql);
    }

}
