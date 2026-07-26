<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Database\DatabaseExportService;
use App\Support\DatabaseExportAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

function refreshDatabaseExportRoleTables(): void
{
    Schema::dropIfExists('model_has_roles');
    Schema::dropIfExists('roles');

    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('guard_name')->default('web');
        $table->timestamps();
    });

    Schema::create('model_has_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('role_id');
        $table->string('model_type');
        $table->unsignedBigInteger('model_id');
        $table->index(['model_id', 'model_type']);
    });
}

function databaseExportUser(int $id, int $roleId): User
{
    $user = new User([
        'name' => "Database Export User {$id}",
        'email' => "database-export-{$id}@example.com",
        'password' => 'password',
    ]);
    $user->id = $id;
    $user->exists = true;

    DB::table('roles')->insert([
        'id' => $roleId,
        'name' => "Role {$roleId}",
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('model_has_roles')->insert([
        'role_id' => $roleId,
        'model_type' => $user->getMorphClass(),
        'model_id' => $user->getKey(),
    ]);

    return $user;
}

test('database export requires authentication', function (): void {
    $this
        ->getJson(route('database.export'))
        ->assertUnauthorized();
});

test('database export is restricted to the administrator role', function (): void {
    refreshDatabaseExportRoleTables();

    $user = databaseExportUser(501, 2);

    expect(DatabaseExportAccess::allowed($user))->toBeFalse();

    $this
        ->actingAs($user)
        ->get(route('database.export'))
        ->assertForbidden();
});

test('administrator can download a generated database export', function (): void {
    refreshDatabaseExportRoleTables();

    $user = databaseExportUser(502, DatabaseExportAccess::ROLE_ID);
    $path = storage_path('framework/testing/database-export-test.sql');
    File::put($path, '-- database export');

    $exporter = Mockery::mock(DatabaseExportService::class);
    $exporter->shouldReceive('export')->once()->andReturn($path);
    app()->instance(DatabaseExportService::class, $exporter);

    $this
        ->actingAs($user)
        ->get(route('database.export'))
        ->assertOk()
        ->assertDownload('database-export-test.sql');
});

test('database export service creates a valid sqlite sql dump', function (): void {
    $databasePath = storage_path('framework/testing/database-export-source.sqlite');
    $exportPath = storage_path('framework/testing/database-export-result.sql');

    File::delete([$databasePath, $exportPath]);
    File::put($databasePath, '');

    config()->set('database.connections.export_test', [
        'driver' => 'sqlite',
        'database' => $databasePath,
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    $connection = DB::connection('export_test');
    $connection->statement('CREATE TABLE export_items (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
    $connection->table('export_items')->insert(['id' => 1, 'name' => 'Backup item']);
    $connection->disconnect();

    app(DatabaseExportService::class)->export('export_test', $exportPath);

    expect(File::get($exportPath))
        ->toContain('CREATE TABLE export_items')
        ->toContain('Backup item');

    File::delete([$databasePath, $exportPath]);
});
