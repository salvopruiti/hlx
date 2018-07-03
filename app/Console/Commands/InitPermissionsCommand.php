<?php

namespace App\Console\Commands;

use App\Permission;
use App\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

class InitPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init-permissions';

    protected $description = 'Initialize Roles ad Permissions';

    public function __construct()
    {
        parent::__construct();
        $this->addOption("refresh", "r", null, "Remove all existing roles and permissions");
    }

    /**
     * @var array
     *
     * @example $name => [$displayName, $description]
     *
     */
    protected $roles = [
        'admin' => ['Amministratore', null],
        'operator' => ['Operatore', null]
    ];

    /**
     * @example $name => [$displayName, $description, $role[]]
     */
    protected $permissions = [
        'full_control' => ['SuperAdmin', null, ['admin']]
    ];

    public function handle()
    {
        $this->getRolesAndPermissions();
        if($this->option('refresh')) {
            Permission::query()->delete();
            Role::query()->delete();
        }

        /** @var Collection|Role[] $roles */
        $roles = collect();
        foreach($this->roles as $name => $data) {
            $roles->put($name, Role::updateOrCreate(['name' => $name], ['display_name' => $data[0], 'description' => $data[1]]));
        }

        foreach($this->permissions as $name => $data) {

            /** @var Permission $permission */
            $permission = Permission::updateOrCreate([
                'name' => $name
            ], [
                'display_name' => $data[0],
                'description' => $data[1]
            ]);

            foreach($data[2] as $roleName) {

                /** @var Role $role */
                $role = $roles->get($roleName);
                if($role && !$role->hasPermission($name))
                    $role->attachPermission($permission);

            }

        }

    }

    private function getRolesAndPermissions()
    {
        try {
            $data = json_decode(file_get_contents(base_path("roles.json")), true);
            if ($data) {

                $this->roles = data_get($data, 'roles', $this->roles);
                $this->permissions = data_get($data, 'permissions', $this->permissions);

            }

        } catch(\Exception $e) {

        }
    }
}
