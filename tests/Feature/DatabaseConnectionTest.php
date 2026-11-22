<?php

use App\Models\DatabaseConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('connections.index'))->assertRedirect(route('login'));
});

test('authenticated users can view their connections', function () {
    $user = User::factory()->create();
    DatabaseConnection::factory()->for($user)->create(['name' => 'Primary']);

    $this->actingAs($user)
        ->get(route('connections.index'))
        ->assertOk();
});

test('users can create a connection with encrypted credentials', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('connections.store'), [
        'name' => 'Local MySQL',
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'app',
        'username' => 'root',
        'password' => 'secret',
    ])->assertRedirect(route('connections.index'));

    $connection = DatabaseConnection::where('name', 'Local MySQL')->first();

    expect($connection)->not->toBeNull();
    expect($connection->password)->toBe('secret');

    $raw = DB::table('database_connections')->where('id', $connection->id)->first();
    expect($raw->password)->not->toBe('secret');
});

test('users can update their own connection', function () {
    $user = User::factory()->create();
    $connection = DatabaseConnection::factory()->for($user)->create(['name' => 'Old name']);

    $this->actingAs($user)->put(route('connections.update', $connection), [
        'name' => 'New name',
        'driver' => $connection->driver,
        'host' => $connection->host,
        'port' => $connection->port,
        'database' => $connection->database,
        'username' => $connection->username,
        'password' => $connection->password,
    ])->assertRedirect(route('connections.index'));

    expect($connection->refresh()->name)->toBe('New name');
});

test('users cannot update another user\'s connection', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $connection = DatabaseConnection::factory()->for($owner)->create();

    $this->actingAs($intruder)->put(route('connections.update', $connection), [
        'name' => 'Hijacked',
        'driver' => $connection->driver,
        'host' => $connection->host,
        'port' => $connection->port,
        'database' => $connection->database,
        'username' => $connection->username,
        'password' => $connection->password,
    ])->assertForbidden();
});

test('users can delete their own connection', function () {
    $user = User::factory()->create();
    $connection = DatabaseConnection::factory()->for($user)->create();

    $this->actingAs($user)->delete(route('connections.destroy', $connection))
        ->assertRedirect(route('connections.index'));

    expect(DatabaseConnection::find($connection->id))->toBeNull();
});

test('users cannot delete another user\'s connection', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $connection = DatabaseConnection::factory()->for($owner)->create();

    $this->actingAs($intruder)->delete(route('connections.destroy', $connection))
        ->assertForbidden();

    expect(DatabaseConnection::find($connection->id))->not->toBeNull();
});

test('test connection endpoint reports success for a reachable sqlite database', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('connections.test'), [
        'name' => 'Local SQLite',
        'driver' => 'sqlite',
        'database' => database_path('database.sqlite'),
    ])->assertOk()->assertJson(['success' => true]);
});

test('test connection endpoint reports failure for unreachable credentials', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('connections.test'), [
        'name' => 'Unreachable MySQL',
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '1',
        'database' => 'does_not_exist',
        'username' => 'root',
        'password' => 'wrong',
    ])->assertOk()->assertJson(['success' => false]);
});
