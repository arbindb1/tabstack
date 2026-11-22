<?php

namespace App\Services;

use App\Models\DatabaseConnection;

class TableDataService
{
    public function __construct(
        private readonly DynamicConnectionService $connections,
        private readonly SchemaIntrospectionService $schema,
    ) {}

    /**
     * Fetch one page of rows from a table on a saved connection.
     *
     * @return array{columns: list<array{name: string, type: string, nullable: bool}>, rows: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function paginate(DatabaseConnection $connection, string $table, int $page, int $perPage): array
    {
        $laravelConnection = $this->connections->connect($connection);

        $query = $laravelConnection->table($table);

        $total = $query->count();

        $rows = $laravelConnection->table($table)
            ->forPage($page, $perPage)
            ->get()
            ->map(fn (object $row) => (array) $row)
            ->values()
            ->all();

        return [
            'columns' => $this->schema->columns($connection, $table),
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
