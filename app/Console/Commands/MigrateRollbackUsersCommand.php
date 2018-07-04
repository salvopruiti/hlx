<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Symfony\Component\Console\Input\InputOption;

class MigrateRollbackUsersCommand extends RollbackCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:rollback-users
                {--force : Force the operation to run when in production.}
                {--pretend : Dump the SQL queries that would be run.}
                {--step : The number of migrations to be reverted.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback the last database migration on the user database';

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

            $this->output->writeln("Utente: <info>".$user->name."</info> - Database: <comment>".$user->getDatabaseName(). "</comment>");

            $this->migrator->setConnection($this->option('database'));

            $this->migrator->rollback(
                $this->getMigrationPaths(), [
                    'pretend' => $this->option('pretend'),
                    'step' => (int) $this->option('step'),
                ]
            );

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

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
            ['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted.'],
        ];
    }

}
