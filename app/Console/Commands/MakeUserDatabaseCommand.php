<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\MySqlConnection;
use Zizaco\Entrust\EntrustRole;

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
     * @return mixed
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        $user = User::findOrFail($userId);

        //database name: user_db_#id#

        $database_name = "user_db_{$user->id}";

        $response = \DB::affectingStatement("CREATE SCHEMA IF NOT EXISTS $database_name");

        config(['database.connections.users.database' => $database_name]);


        $migrator = app('migrator');
        $migrator->setConnection('users');

        if(!$migrator->repositoryExists())
            $migrator->getRepository()->createRepository();

        $migrator->run(database_path('user_migrations'));

        foreach ($migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }

        $seeder = new \DefaultConfigurationSeeder();
        $seeder->run();

    }
}
