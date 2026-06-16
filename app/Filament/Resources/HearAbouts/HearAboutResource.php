<?php

namespace App\Filament\Resources\HearAbouts;

use App\Filament\Resources\HearAbouts\Pages\CreateHearAbout;
use App\Filament\Resources\HearAbouts\Pages\EditHearAbout;
use App\Filament\Resources\HearAbouts\Pages\ListHearAbouts;
use App\Filament\Resources\HearAbouts\Schemas\HearAboutForm;
use App\Filament\Resources\HearAbouts\Tables\HearAboutsTable;
use App\Models\HearAbout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HearAboutResource extends Resource
{
    protected static ?string $model = HearAbout::class;
    protected static ?int $navigationSort = 5;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Newspaper;

    public static function form(Schema $schema): Schema
    {
        return HearAboutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HearAboutsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHearAbouts::route('/'),
            'create' => CreateHearAbout::route('/create'),
            'edit' => EditHearAbout::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.hear_about');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.hear_about');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.hear_about');
    }

    public static function getLabel(): string
    {
        return __('admin.hear_about');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.hear_about');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.hear_about');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.settings');
    }
}
