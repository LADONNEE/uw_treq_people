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

    public function __construct()
    {
        $this->edw = \App::make('edw');
    }

    /**
     * EDW data for current employee position records from Workday data
     * @return array
     */
    public function getCollegePositions()
    {
        $match = "'%UAA%'";
        $validity = Carbon::now()->addYears(1);
        $sql = sqlInclude(__DIR__ .'/persons.sql', [
            '__MATCH__' => $match,
            '__VALIDITY__' => $validity->format('Y-m-d')
        ]);
        return $this->edw->fetchAssoc($sql);
    }

}
