<?php

use App\Http\Controllers\DatabaseConnectionController;
use App\Http\Controllers\SqlQueryController;
use App\Http\Controllers\TabController;
use App\Http\Controllers\TableDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('connections/test', [DatabaseConnectionController::class, 'test'])->name('connections.test');
    Route::get('connections/{connection}/schema', [DatabaseConnectionController::class, 'schema'])->name('connections.schema');

    Route::get('connections/{connection}/tabs', [TabController::class, 'index'])->name('connections.tabs.index');
    Route::post('connections/{connection}/tabs', [TabController::class, 'store'])->name('connections.tabs.store');
    Route::patch('connections/{connection}/tabs/{tab}', [TabController::class, 'update'])->name('connections.tabs.update');
    Route::delete('connections/{connection}/tabs/{tab}', [TabController::class, 'destroy'])->name('connections.tabs.destroy');

    Route::get('connections/{connection}/tables/{table}/rows', [TableDataController::class, 'rows'])->name('connections.tables.rows');
    Route::post('connections/{connection}/query', [SqlQueryController::class, 'run'])->name('connections.query');

    Route::resource('connections', DatabaseConnectionController::class);
});
