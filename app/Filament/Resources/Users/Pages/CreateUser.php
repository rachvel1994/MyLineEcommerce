<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = UserResource::class;
}
