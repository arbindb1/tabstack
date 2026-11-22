<?php

use App\Models\DatabaseConnection;
use App\Models\User;
use App\Services\DynamicConnectionService;
use App\Services\SchemaIntrospectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Create a standalone sqlite file, migrate a table into it directly (not via
 * the app's own test connection), and return the path. The caller is
 * responsible for deleting it.
 */
function createIntrospectableSqliteDatabase(string $table, Closure $define): string
{
    $path = storage_path('framework/testing/'.Str::random(16).'.sqlite');

    touch($path);

    $connectionName = 'introspectable_'.Str::random(8);

    config(["database.connections.{$connectionName}" => [
        'driver' => 'sqlite',
        'database' => $path,
    ]]);

    Schema::connection($connectionName)->create($table, $define);

    DB::purge($connectionName);
    config(["database.connections.{$connectionName}" => null]);

    return $path;
}

test('guests are redirected to the login page', function () {
    $connection = DatabaseConnection::factory()->create();

    $this->get(route('connections.schema', $connection))->assertRedirect(route('login'));
});

test('users cannot view another user\'s schema', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $connection = DatabaseConnection::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('connections.schema', $connection))
        ->assertForbidden();
});

test('schema introspection returns tables and columns for a sqlite connection', function () {
    $path = createIntrospectableSqliteDatabase('widgets', function ($table) {
        $table->id();
        $table->string('name');
        $table->integer('quantity')->nullable();
    });

    $user = User::factory()->create();
    $connection = DatabaseConnection::factory()->for($user)->create([
        'driver' => 'sqlite',
        'database' => $path,
    ]);

    $tree = app(SchemaIntrospectionService::class)->tree($connection);

    $widgets = collect($tree)->firstWhere('name', 'widgets');

    expect($widgets)->not->toBeNull();

    $columns = collect($widgets['columns'])->keyBy('name');

    expect($columns->has('id'))->toBeTrue();
    expect($columns->has('name'))->toBeTrue();
    expect($columns->has('quantity'))->toBeTrue();
    expect($columns['quantity']['nullable'])->toBeTrue();
    expect($columns['name']['nullable'])->toBeFalse();

    DB::purge(app(DynamicConnectionService::class)->nameFor($connection));
    unlink($path);
});

test('the schema endpoint returns the introspected tree as json', function () {
    $path = createIntrospectableSqliteDatabase('gadgets', function ($table) {
        $table->id();
        $table->string('label');
    });

    $user = User::factory()->create();
    $connection = DatabaseConnection::factory()->for($user)->create([
        'driver' => 'sqlite',
        'database' => $path,
    ]);

    $response = $this->actingAs($user)
        ->get(route('connections.schema', $connection))
        ->assertOk()
        ->json();

    $gadgets = collect($response['tables'])->firstWhere('name', 'gadgets');

    expect($gadgets)->not->toBeNull();
    expect(collect($gadgets['columns'])->pluck('name'))->toContain('id', 'label');

    DB::purge(app(DynamicConnectionService::class)->nameFor($connection));
    unlink($path);
});
