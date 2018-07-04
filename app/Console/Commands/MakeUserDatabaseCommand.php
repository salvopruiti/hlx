<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class MakeUserDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user-database {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Database Structure for specified user';

    protected $resolver;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        /** @var User $user */
        $user = User::findOrFail($userId);

        if(!$user->userDatabaseExists())
            $user->createUserDatabase();

        $user->setUserDatabase();
        $this->migrate();
        $this->seed();
    }

    private function migrate()
    {
        $migrator = app('migrator');
        $migrator->setConnection('users');

        if(!$migrator->repositoryExists())
            $migrator->getRepository()->createRepository();

        $migrator->run(database_path('user_migrations'));

        foreach ($migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }
    }

    private function seed()
    {
        $this->resolver = app('db');
        $this->resolver->setDefaultConnection("users");

        $seeder = new \DefaultConfigurationSeeder();
        $seeder->setContainer(app())->setCommand($this);
        $seeder->__invoke();

    }
}
