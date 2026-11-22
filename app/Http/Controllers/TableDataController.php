<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesConnectionOwnership;
use App\Models\DatabaseConnection;
use App\Services\TableDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableDataController extends Controller
{
    use AuthorizesConnectionOwnership;

    /**
     * Return one page of rows for a table on the given connection.
     */
    public function rows(Request $request, DatabaseConnection $connection, string $table, TableDataService $service): JsonResponse
    {
        $this->authorizeOwnership($request, $connection);

        $data = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ]);

        $page = $data['page'] ?? 1;
        $perPage = $data['per_page'] ?? 50;

        return response()->json($service->paginate($connection, $table, $page, $perPage));
    }
}
