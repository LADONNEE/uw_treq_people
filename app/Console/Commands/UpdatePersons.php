<?php

/**
 * Console command to update share uw_persons data from UW EDW
 * @author ladonnee
 */

namespace App\Console\Commands;

use App\Edw\PersonsDataSource;
use App\Updaters\Persons\PersonsUpdateJob;
use App\Updaters\EdwParser;
use Illuminate\Console\Command;

class UpdatePersons extends Command
{

    protected $signature = 'update:persons';
    protected $description = 'Update shared.uw_persons data';
    protected $jobClass = 'App\Updaters\Persons\PersonsUpdateJob';

    public function handle(PersonsDataSource $edw, EdwParser $parser)
    {
        $job = new PersonsUpdateJob($edw, $parser);
        $job->run();
    }
}
