<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @param string $connection_name
     * @return \Illuminate\Database\Connection
     */
    public function setUserDatabase($connection_name = 'users')
    {
        $database_name = $this->getDatabaseName();
        config(["database.connections.$connection_name.database" => $database_name]);
        ($connection = \DB::connection($connection_name))->disconnect();

        $connection->setDatabaseName($database_name);

        return $connection;
    }

    public function userDatabaseExists() : bool
    {
        $database_name = $this->getDatabaseName();
        return (bool)\DB::connection('users')->selectOne("SELECT * FROM information_schema.SCHEMATA where SCHEMA_NAME = ?", [$database_name]);
    }

    public function createUserDatabase()
    {
        $database_name = $this->getDatabaseName();
        \DB::affectingStatement("CREATE SCHEMA IF NOT EXISTS $database_name");
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        $database_name = "user_db_{$this->id}";
        return $database_name;
    }

    public function getDatabaseVersion(): string
    {
        if($this->userDatabaseExists())
            return $this->setUserDatabase()->selectOne("SELECT max(batch) as version from migrations")->version / 10;

        return '---';
    }
}
