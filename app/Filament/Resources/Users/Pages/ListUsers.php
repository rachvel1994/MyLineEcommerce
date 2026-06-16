<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\HasUserHeaderActions;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    use HasUserHeaderActions;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ...static::userHeaderActions(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $baseQuery = User::query();

        $roles = Role::query()->pluck('name');

        foreach ($roles as $roleName) {
            $label = ucfirst($roleName);

            $tabs[$label] = Tab::make($label)
                ->badge((clone $baseQuery)->role($roleName)->count())
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->role($roleName)
                );
        }

        return $tabs;
    }
}
