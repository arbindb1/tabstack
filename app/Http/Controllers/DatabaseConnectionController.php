<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesConnectionOwnership;
use App\Http\Requests\DatabaseConnectionRequest;
use App\Models\DatabaseConnection;
use App\Services\DynamicConnectionService;
use App\Services\SchemaIntrospectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DatabaseConnectionController extends Controller
{
    use AuthorizesConnectionOwnership;

    /**
     * List the authenticated user's saved connections.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('connections/index', [
            'connections' => $request->user()
                ->databaseConnections()
                ->orderBy('name')
                ->get(['id', 'name', 'driver', 'database', 'created_at']),
        ]);
    }

    /**
     * Show the form for creating a new connection.
     */
    public function create(): Response
    {
        return Inertia::render('connections/create');
    }

    /**
     * Save a new connection for the authenticated user.
     */
    public function store(DatabaseConnectionRequest $request): RedirectResponse
    {
        $request->user()->databaseConnections()->create($request->validated());

        return to_route('connections.index');
    }

    /**
     * Show the workspace (schema explorer) for a connection.
     */
    public function show(Request $request, DatabaseConnection $connection): Response
    {
        $this->authorizeOwnership($request, $connection);

        return Inertia::render('connections/show', [
            'connection' => $connection->only(['id', 'name', 'driver', 'database']),
        ]);
    }

    /**
     * Show the form for editing a connection.
     */
    public function edit(Request $request, DatabaseConnection $connection): Response
    {
        $this->authorizeOwnership($request, $connection);

        return Inertia::render('connections/edit', [
            'connection' => $connection->only(['id', 'name', 'driver', 'host', 'port', 'database', 'username']),
        ]);
    }

    /**
     * Update an existing connection.
     */
    public function update(DatabaseConnectionRequest $request, DatabaseConnection $connection): RedirectResponse
    {
        $this->authorizeOwnership($request, $connection);

        $connection->update($request->validated());

        return to_route('connections.index');
    }

    /**
     * Delete a connection.
     */
    public function destroy(Request $request, DatabaseConnection $connection): RedirectResponse
    {
        $this->authorizeOwnership($request, $connection);

        $connection->delete();

        return to_route('connections.index');
    }

    /**
     * Attempt a live connection with the given (not necessarily saved) credentials.
     */
    public function test(DatabaseConnectionRequest $request, DynamicConnectionService $service): JsonResponse
    {
        $success = $service->testConnection([
            'driver' => $request->string('driver')->toString(),
            'host' => $request->input('host'),
            'port' => $request->input('port'),
            'database' => $request->string('database')->toString(),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ]);

        return response()->json(['success' => $success]);
    }

    /**
     * Return the schema tree (tables and columns) for a connection as JSON.
     */
    public function schema(Request $request, DatabaseConnection $connection, SchemaIntrospectionService $service): JsonResponse
    {
        $this->authorizeOwnership($request, $connection);

        return response()->json(['tables' => $service->tree($connection)]);
    }
}
