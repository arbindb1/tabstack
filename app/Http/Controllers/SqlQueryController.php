<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesConnectionOwnership;
use App\Models\DatabaseConnection;
use App\Services\QueryExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class SqlQueryController extends Controller
{
    use AuthorizesConnectionOwnership;

    /**
     * Run a raw SQL statement against the connection and return the result
     * grid along with the query execution timer.
     */
    public function run(Request $request, DatabaseConnection $connection, QueryExecutionService $service): JsonResponse
    {
        $this->authorizeOwnership($request, $connection);

        $data = $request->validate([
            'sql' => ['required', 'string'],
        ]);

        try {
            return response()->json($service->run($connection, $data['sql']));
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
