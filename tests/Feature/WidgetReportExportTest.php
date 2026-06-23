<?php

use App\Enums\WidgetReport;
use App\Http\Requests\WidgetReportExportRequest;
use App\Models\User;
use App\Services\Reports\WidgetReportExportService;
use App\Support\WidgetReportAccess;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

afterEach(function (): void {
    Carbon::setTestNow();
});

function refreshWidgetReportRoleTables(): void
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

function widgetReportUser(int $id): User
{
    $user = new User([
        'name' => "Report User {$id}",
        'email' => "report-user-{$id}@example.com",
        'password' => 'password',
    ]);
    $user->id = $id;
    $user->exists = true;

    return $user;
}

function assignWidgetReportRole(User $user, int $roleId): void
{
    DB::table('roles')->insertOrIgnore([
        'id' => $roleId,
        'name' => "Role {$roleId}",
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('model_has_roles')->insertOrIgnore([
        'role_id' => $roleId,
        'model_type' => $user->getMorphClass(),
        'model_id' => $user->getKey(),
    ]);
}

test('widget report options default to all widgets', function (): void {
    app()->setLocale('en');

    $options = WidgetReport::options();
    $labels = array_values($options);

    expect($options)
        ->toHaveKey(WidgetReport::ALL)
        ->not->toHaveKey('product_overview')
        ->not->toHaveKey('product_list')
        ->not->toHaveKey('product_chart')
        ->and($options[WidgetReport::ALL])
        ->toBe('All widgets')
        ->and(array_keys($options))
        ->toContain(WidgetReport::ProductStats->value, WidgetReport::TopModels->value)
        ->and($labels)
        ->toHaveCount(count(array_unique($labels)));
});

test('widget report service resolves the current month by default', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-06-23 12:00:00'));

    [$fromDate, $toDate] = app(WidgetReportExportService::class)->resolveDateRange(null, null);

    expect($fromDate->format('Y-m-d H:i:s'))
        ->toBe('2026-06-01 00:00:00')
        ->and($toDate->format('Y-m-d H:i:s'))
        ->toBe('2026-06-30 23:59:59');
});

test('widget report service selects all reports or a single report', function (): void {
    $service = app(WidgetReportExportService::class);

    expect($service->selectedReports(WidgetReport::ALL))
        ->toHaveCount(count(WidgetReport::cases()))
        ->and($service->selectedReports(WidgetReport::TopModels->value))
        ->toBe([WidgetReport::TopModels]);
});

test('widget report file name includes widget and date range', function (): void {
    $fileName = app(WidgetReportExportService::class)->fileName(
        WidgetReport::TopModels->value,
        Carbon::parse('2026-06-01'),
        Carbon::parse('2026-06-30'),
    );

    expect($fileName)->toBe('widget-reports-top_models-2026-06-01-2026-06-30.xlsx');
});

test('widget report request validates widget and date filters', function (): void {
    $rules = (new WidgetReportExportRequest)->rules();

    expect(Validator::make([
        'widget' => WidgetReport::ALL,
        'from_date' => '2026-06-01',
        'to_date' => '2026-06-30',
    ], $rules)->passes())->toBeTrue();

    expect(Validator::make([
        'widget' => 'unknown_widget',
        'from_date' => '2026-06-01',
        'to_date' => '2026-06-30',
    ], $rules)->fails())->toBeTrue();

    expect(Validator::make([
        'widget' => WidgetReport::ProductStats->value,
        'from_date' => '2026-06-30',
        'to_date' => '2026-06-01',
    ], $rules)->fails())->toBeTrue();
});

test('widget report export access is limited to spatie role id 1', function (): void {
    refreshWidgetReportRoleTables();

    $admin = widgetReportUser(101);
    $otherUser = widgetReportUser(102);

    assignWidgetReportRole($admin, WidgetReportAccess::ROLE_ID);
    assignWidgetReportRole($otherUser, 2);

    expect(WidgetReportAccess::allowed($admin))
        ->toBeTrue()
        ->and(WidgetReportAccess::allowed($otherUser))
        ->toBeFalse();
});

test('widget report export route forbids authenticated users without role id 1', function (): void {
    refreshWidgetReportRoleTables();

    $user = widgetReportUser(201);
    assignWidgetReportRole($user, 2);

    $this
        ->actingAs($user)
        ->get(route('widget-reports.export', [
            'widget' => WidgetReport::CashDrawer->value,
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
        ]))
        ->assertForbidden();
});

test('widget report service can build a filtered workbook', function (): void {
    app()->setLocale('en');

    Schema::dropIfExists('cash_movements');
    Schema::dropIfExists('cash_drawers');

    Schema::create('cash_drawers', function (Blueprint $table): void {
        $table->id();
        $table->decimal('opening_balance', 14, 2)->default(0);
        $table->decimal('current_balance', 14, 2)->default(0);
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

    DB::table('cash_drawers')->insert([
        'id' => 1,
        'opening_balance' => 100,
        'current_balance' => 180,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('cash_movements')->insert([
        [
            'cash_drawer_id' => 1,
            'direction' => 'in',
            'amount' => 50,
            'moved_at' => '2026-06-10 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'cash_drawer_id' => 1,
            'direction' => 'out',
            'amount' => 20,
            'moved_at' => '2026-06-11 12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $spreadsheet = app(WidgetReportExportService::class)->spreadsheet(
        WidgetReport::CashDrawer->value,
        Carbon::parse('2026-06-01')->startOfDay(),
        Carbon::parse('2026-06-30')->endOfDay(),
        new User(['name' => 'Report User']),
    );

    expect($spreadsheet->getSheetCount())
        ->toBe(2)
        ->and($spreadsheet->getSheet(1)->getCell('A2')->getValue())
        ->toBe('Start of day')
        ->and($spreadsheet->getSheet(1)->getCell('B2')->getValue())
        ->toBe(100.0);
});
