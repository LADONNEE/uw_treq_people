<?php

namespace App\Updaters\Persons;

use App\Edw\PersonsDataSource;
use App\Updaters\EdwParser;

class PersonsUpdateJob
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
        (new ImportUwPersonsTask($this->edw, $this->parser))->run();
    }

    // public function importAdHocUser($persondata)
    // {
    //     (new ImportUwPersonsTask($this->edw, $this->parser))->importAdHocUser($persondata);
    // }


}
