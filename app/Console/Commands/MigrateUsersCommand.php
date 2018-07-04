<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Symfony\Component\Console\Input\InputOption;

class MigrateUsersCommand extends MigrateCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:users 
                {--force : Force the operation to run when in production.}
                {--pretend : Dump the SQL queries that would be run.}
                {--seed : Indicates if the seed task should be re-run.}
                {--step : Force the migrations to be run so they can be rolled back individually.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations on users database';

    public function __construct()
    {
        $migrator = app('migrator');

        parent::__construct($migrator);
    }

    public function handle()
    {
        if(!$this->hasOption('database')) $this->addOption("database", null, InputOption::VALUE_OPTIONAL, '', 'users');

        if (! $this->confirmToProceed()) {
            return;
        }

        $users = User::all();

        foreach($users as $user) {

            if(!$user->userDatabaseExists()) $user->createUserDatabase();
            $user->setUserDatabase();

            $this->prepareDatabase();

            $this->output->writeln("Utente: <info>".$user->name."</info> - Database: <comment>".$user->getDatabaseName(). "</comment>");

            $this->migrator->run($this->getMigrationPaths(), [
                'pretend' => $this->option('pretend'),
                'step' => $this->option('step'),
            ]);

            foreach ($this->migrator->getNotes() as $note) {
                $this->output->writeln("    ".$note);
            }

            $this->output->write("\n");
        }


    }

    protected function getMigrationPaths()
    {
        if(!$this->hasOption('path')) $this->addOption("path", null, InputOption::VALUE_OPTIONAL, '', database_path('user_migrations'));
        return parent::getMigrationPaths();
    }

    protected function usingRealPath()
    {
        return true;
    }

}
