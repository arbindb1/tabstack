<?php

namespace App\Models;

use Database\Factories\DatabaseConnectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $driver
 * @property string|null $host
 * @property string|null $port
 * @property string $database
 * @property string|null $username
 * @property string|null $password
 */
class DatabaseConnection extends Model
{
    /** @use HasFactory<DatabaseConnectionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'driver',
        'host',
        'port',
        'database',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'host' => 'encrypted',
            'port' => 'encrypted',
            'database' => 'encrypted',
            'username' => 'encrypted',
            'password' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Tab, $this>
     */
    public function tabs(): HasMany
    {
        return $this->hasMany(Tab::class)->orderBy('position');
    }

    /**
     * Connection attributes in the shape DynamicConnectionService expects.
     *
     * @return array{driver: string, host: ?string, port: ?string, database: string, username: ?string, password: ?string}
     */
    public function toConnectionConfig(): array
    {
        return [
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
