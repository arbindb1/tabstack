<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesConnectionOwnership;
use App\Models\DatabaseConnection;
use App\Models\Tab;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabController extends Controller
{
    use AuthorizesConnectionOwnership;

    /**
     * List the open tabs for a connection, in display order.
     */
    public function index(Request $request, DatabaseConnection $connection): JsonResponse
    {
        $this->authorizeOwnership($request, $connection);

        return response()->json(['tabs' => $connection->tabs()->get()]);
    }

    /**
     * Open a new tab (table data view or SQL editor) for a connection.
     */
    public function store(Request $request, DatabaseConnection $connection): JsonResponse
    {
        $this->authorizeOwnership($request, $connection);

        $data = $request->validate([
            'type' => ['required', 'in:table,sql'],
            'label' => ['required', 'string', 'max:255'],
            'table_name' => ['required_if:type,table', 'nullable', 'string', 'max:255'],
        ]);

        $tab = $connection->tabs()->create([
            'type' => $data['type'],
            'label' => $data['label'],
            'table_name' => $data['table_name'] ?? null,
            'sql_body' => $data['type'] === 'sql' ? '' : null,
            'position' => (int) $connection->tabs()->max('position') + 1,
            'is_active' => true,
        ]);

        $connection->tabs()->whereKeyNot($tab->id)->update(['is_active' => false]);

        return response()->json(['tab' => $tab], 201);
    }

    /**
     * Rename a tab, edit its SQL body, reorder it, or mark it active.
     */
    public function update(Request $request, DatabaseConnection $connection, Tab $tab): JsonResponse
    {
        $this->authorizeOwnership($request, $connection);
        $this->authorizeTab($connection, $tab);

        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'sql_body' => ['sometimes', 'nullable', 'string'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $tab->update($data);

        if ($request->boolean('is_active')) {
            $connection->tabs()->whereKeyNot($tab->id)->update(['is_active' => false]);
        }

        return response()->json(['tab' => $tab->fresh()]);
    }

    /**
     * Close a tab.
     */
    public function destroy(Request $request, DatabaseConnection $connection, Tab $tab): JsonResponse
    {
        $this->authorizeOwnership($request, $connection);
        $this->authorizeTab($connection, $tab);

        $tab->delete();

        return response()->json(['success' => true]);
    }

    protected function authorizeTab(DatabaseConnection $connection, Tab $tab): void
    {
        abort_unless($tab->database_connection_id === $connection->id, 404);
    }
}
