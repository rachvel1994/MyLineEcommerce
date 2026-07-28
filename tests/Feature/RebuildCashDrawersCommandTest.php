<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('cash_movements');
    Schema::dropIfExists('cash_drawers');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
    });

    Schema::create('cash_drawers', function (Blueprint $table): void {
        $table->id();
        $table->decimal('opening_balance', 12, 2)->default(0);
        $table->decimal('current_balance', 12, 2)->default(0);
        $table->dateTime('opened_at')->nullable();
        $table->unsignedBigInteger('opened_by')->nullable();
        $table->dateTime('closed_at')->nullable();
        $table->unsignedBigInteger('closed_by')->nullable();
        $table->timestamps();
    });

    Schema::create('cash_movements', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('cash_drawer_id');
        $table->string('direction', 10);
        $table->decimal('amount', 14, 2);
        $table->dateTime('moved_at');
        $table->timestamps();
    });

    Schema::enableForeignKeyConstraints();

    DB::table('users')->insert(['id' => 4, 'name' => 'Administrator']);
    DB::table('cash_drawers')->insert([
        'id' => 1,
        'opening_balance' => 100,
        'current_balance' => 999,
        'opened_at' => '2026-07-01 08:00:00',
        'opened_by' => 4,
        'closed_at' => null,
        'closed_by' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('cash_movements')->insert([
        [
            'cash_drawer_id' => 1,
            'direction' => 'in',
            'amount' => 50,
            'moved_at' => '2026-07-01 10:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'cash_drawer_id' => 1,
            'direction' => 'out',
            'amount' => 20,
            'moved_at' => '2026-07-01 11:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'cash_drawer_id' => 1,
            'direction' => 'adjust',
            'amount' => -5,
            'moved_at' => '2026-07-01 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'cash_drawer_id' => 1,
            'direction' => 'in',
            'amount' => 10,
            'moved_at' => '2026-07-02 10:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

test('cash drawer rebuild is a dry run unless apply is provided', function (): void {
    $this
        ->artisan('cash-drawers:rebuild-by-movements', [
            '--from' => '2026-07-01',
            '--user' => 4,
        ])
        ->expectsOutputToContain('Dry run only')
        ->assertSuccessful();

    expect((float) DB::table('cash_drawers')->where('id', 1)->value('current_balance'))
        ->toBe(999.0)
        ->and(DB::table('cash_drawers')->count())
        ->toBe(1);
});

test('cash drawer rebuild assigns movements by date and recalculates balances', function (): void {
    $this
        ->artisan('cash-drawers:rebuild-by-movements', [
            '--from' => '2026-07-01',
            '--to' => '2026-07-02',
            '--user' => 4,
            '--apply' => true,
        ])
        ->assertSuccessful();

    $drawers = DB::table('cash_drawers')->orderBy('opened_at')->get();

    expect($drawers)
        ->toHaveCount(2)
        ->and((float) $drawers[0]->opening_balance)->toBe(100.0)
        ->and((float) $drawers[0]->current_balance)->toBe(125.0)
        ->and($drawers[0]->closed_at)->not->toBeNull()
        ->and((float) $drawers[1]->opening_balance)->toBe(125.0)
        ->and((float) $drawers[1]->current_balance)->toBe(135.0)
        ->and($drawers[1]->closed_at)->toBeNull()
        ->and(DB::table('cash_movements')->where('cash_drawer_id', $drawers[0]->id)->count())->toBe(3)
        ->and(DB::table('cash_movements')->where('cash_drawer_id', $drawers[1]->id)->count())->toBe(1);
});

test('cash drawer rebuild validates the operator user', function (): void {
    $this
        ->artisan('cash-drawers:rebuild-by-movements', [
            '--user' => 999,
            '--apply' => true,
        ])
        ->expectsOutputToContain('must reference an existing user')
        ->assertFailed();
});
