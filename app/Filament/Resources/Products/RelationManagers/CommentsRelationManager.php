<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('author_id')
                    ->default(auth()->id()),

                Hidden::make('author_type')
                    ->default(User::class),

                Textarea::make('body')
                    ->label(__('admin.comment'))
                    ->required()
                    ->columnSpanFull()
                    ->rows(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('author'))
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('author.name')
                    ->label(__('admin.user'))
                    ->searchable(),
                TextColumn::make('body')
                    ->label(__('admin.comment'))
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('admin.service_comment')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.service_comment');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('admin.service_comment');
    }

    protected static function getPluralRecordLabel(): ?string
    {
        return __('admin.service_comment');
    }

    public static function getPluralModelLabel(): ?string
    {
        return __('admin.service_comment');
    }

    public static function getPluralLabel(): ?string
    {
        return __('admin.service_comment');
    }

    public static function getModelLabel(): ?string
    {
        return strtolower(__('admin.service_comment'));
    }
}
