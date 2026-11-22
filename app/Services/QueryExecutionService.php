<?php

namespace App\Services;

use App\Models\DatabaseConnection;
use InvalidArgumentException;

class QueryExecutionService
{
    public function __construct(private readonly DynamicConnectionService $connections) {}

    /**
     * Run a single raw SQL statement against a saved connection and time it.
     *
     * @return array{columns: list<string>, rows: list<array<string, mixed>>, row_count: int, elapsed_ms: float}
     */
    public function run(DatabaseConnection $connection, string $sql): array
    {
        $sql = trim($sql);

        if ($sql === '') {
            throw new InvalidArgumentException('No SQL statement to execute.');
        }

        if ($this->containsMultipleStatements($sql)) {
            throw new InvalidArgumentException('Only a single SQL statement may be executed at a time.');
        }

        $pdo = $this->connections->connect($connection)->getPdo();

        $start = microtime(true);

        $statement = $pdo->prepare($sql);
        $statement->execute();

        $elapsedMs = round((microtime(true) - $start) * 1000, 2);

        if ($statement->columnCount() > 0) {
            $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'columns' => $rows === [] ? [] : array_keys($rows[0]),
                'rows' => $rows,
                'row_count' => count($rows),
                'elapsed_ms' => $elapsedMs,
            ];
        }

        return [
            'columns' => [],
            'rows' => [],
            'row_count' => $statement->rowCount(),
            'elapsed_ms' => $elapsedMs,
        ];
    }

    /**
     * Detect more than one statement, ignoring semicolons inside quoted
     * strings/identifiers and a single trailing terminator.
     */
    protected function containsMultipleStatements(string $sql): bool
    {
        $statements = 0;
        $inString = null;
        $sawStatementContent = false;

        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inString !== null) {
                if ($char === '\\' && $inString !== '`') {
                    $i++;
                } elseif ($char === $inString) {
                    $inString = null;
                }

                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $inString = $char;
                $sawStatementContent = true;

                continue;
            }

            if ($char === ';') {
                if ($sawStatementContent) {
                    $statements++;
                }

                $sawStatementContent = false;

                continue;
            }

            if (! ctype_space($char)) {
                $sawStatementContent = true;
            }
        }

        if ($sawStatementContent) {
            $statements++;
        }

        return $statements > 1;
    }
}
