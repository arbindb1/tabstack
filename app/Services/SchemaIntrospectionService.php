<?php

namespace App\Services;

use App\Models\DatabaseConnection;
use Illuminate\Support\Facades\Schema;

class SchemaIntrospectionService
{
    public function __construct(private readonly DynamicConnectionService $connections) {}

    /**
     * Build a tree of tables and columns for a saved connection.
     *
     * @return list<array{name: string, columns: list<array{name: string, type: string, nullable: bool}>}>
     */
    public function tree(DatabaseConnection $connection): array
    {
        $this->connections->connect($connection);

        $name = $this->connections->nameFor($connection);

        return collect(Schema::connection($name)->getTables())
            ->map(fn (array $table) => $this->describeTable($name, $table['name']))
            ->values()
            ->all();
    }

    /**
     * Columns for a single table on a saved connection.
     *
     * @return list<array{name: string, type: string, nullable: bool}>
     */
    public function columns(DatabaseConnection $connection, string $table): array
    {
        $this->connections->connect($connection);

        return $this->describeTable($this->connections->nameFor($connection), $table)['columns'];
    }

    /**
     * @return array{name: string, columns: list<array{name: string, type: string, nullable: bool}>}
     */
    protected function describeTable(string $connectionName, string $table): array
    {
        return [
            'name' => $table,
            'columns' => collect(Schema::connection($connectionName)->getColumns($table))
                ->map(fn (array $column) => [
                    'name' => $column['name'],
                    'type' => $column['type_name'],
                    'nullable' => $column['nullable'],
                ])
                ->values()
                ->all(),
        ];
    }
}
