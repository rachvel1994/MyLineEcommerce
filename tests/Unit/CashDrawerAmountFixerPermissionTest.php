<?php

declare(strict_types=1);

use App\Policies\CashDrawerPolicy;
use Illuminate\Foundation\Auth\User as AuthUser;

test('cash drawer amount fixer has separate view and use permissions', function (): void {
    $user = Mockery::mock(AuthUser::class);
    $user->shouldReceive('can')
        ->with('CanViewAmountFixer:CashDrawer')
        ->once()
        ->andReturnTrue();
    $user->shouldReceive('can')
        ->with('CanUseAmountFixer:CashDrawer')
        ->once()
        ->andReturnFalse();

    $policy = new CashDrawerPolicy;

    expect($policy->canViewAmountFixer($user))->toBeTrue()
        ->and($policy->canUseAmountFixer($user))->toBeFalse();
});
