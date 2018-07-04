<?php

namespace App\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeUserMigrationCommand extends MigrateMakeCommand
{
    protected $signature = 'make:user-migration {name : The name of the migration.}
        {--create= : The table to be created.}
        {--table= : The table to migrate.}';


    protected $description = "Create a new migration file for the users database";

    protected function usingRealPath()
    {
        return true;
    }

    protected function getMigrationPath()
    {
        $this->addOption("path", null, InputOption::VALUE_OPTIONAL, '', database_path('user_migrations'));
        return parent::getMigrationPath();
    }
}
