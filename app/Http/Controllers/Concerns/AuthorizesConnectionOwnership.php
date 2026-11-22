<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DatabaseConnection;
use Illuminate\Http\Request;

trait AuthorizesConnectionOwnership
{
    protected function authorizeOwnership(Request $request, DatabaseConnection $connection): void
    {
        abort_unless($connection->user_id === $request->user()->id, 403);
    }
}
