<?php

namespace App\Services;

use App\Models\DatabaseConnection;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class DynamicConnectionService
{
    /**
     * Open a runtime connection to a saved DatabaseConnection.
     */
    public function connect(DatabaseConnection $connection): Connection
    {
        $name = $this->nameFor($connection);

        config(["database.connections.{$name}" => $this->toLaravelConfig($connection->toConnectionConfig())]);

        return DB::connection($name);
    }

    /**
     * The Laravel connection name used for a saved DatabaseConnection.
     */
    public function nameFor(DatabaseConnection $connection): string
    {
        return $this->connectionName($connection->id);
    }

    /**
     * Attempt to open a connection from raw (unsaved) attributes and report
     * whether it succeeds, without persisting anything.
     *
     * @param  array{driver: string, host: ?string, port: ?string, database: string, username: ?string, password: ?string}  $attributes
     */
    public function testConnection(array $attributes): bool
    {
        $name = $this->connectionName('test_'.uniqid());

        config(["database.connections.{$name}" => $this->toLaravelConfig($attributes)]);

        try {
            DB::connection($name)->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        } finally {
            DB::purge($name);
            config(["database.connections.{$name}" => null]);
        }
    }

    protected function connectionName(int|string $suffix): string
    {
        return "dynamic_{$suffix}";
    }

    /**
     * @param  array{driver: string, host: ?string, port: ?string, database: string, username: ?string, password: ?string}  $attributes
     * @return array<string, mixed>
     */
    protected function toLaravelConfig(array $attributes): array
    {
        return match ($attributes['driver']) {
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => $attributes['database'],
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => $attributes['host'],
                'port' => $attributes['port'],
                'database' => $attributes['database'],
                'username' => $attributes['username'],
                'password' => $attributes['password'],
                'charset' => 'utf8mb4',
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => $attributes['host'],
                'port' => $attributes['port'],
                'database' => $attributes['database'],
                'username' => $attributes['username'],
                'password' => $attributes['password'],
                'charset' => 'utf8',
            ],
            default => throw new InvalidArgumentException("Unsupported driver [{$attributes['driver']}]."),
        };
    }
}
