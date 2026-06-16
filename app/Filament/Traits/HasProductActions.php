<?php

namespace App\Filament\Traits;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

trait HasProductActions
{
    /**
     * Ability naming strategy.
     * Example: CanViewPdf -> CanViewPdf:Product
     */
    protected static function productAbility(string $ability): string
    {
        return "{$ability}:Product";
    }

    /**
     * Central ability check for current user.
     */
    protected static function canAbility(string $ability): bool
    {
        return canAbility(static::productAbility($ability));
    }

    /**
     *  Guarantee PDF (opens in new tab)
     */
    protected static function warrantyPdfAction(): Action
    {
        return Action::make('guarantee')
            ->label('PDF')
            ->icon(Heroicon::DocumentText)
            ->color('success')
            ->schema([
                Select::make('lang')
                    ->label(__('admin.pdf_language'))
                    ->options([
                        'ka' => 'ქართული',
                        'en' => 'English',
                        'ru' => 'Русский',
                    ])
                    ->default(app()->getLocale())
                    ->required()
                    ->native(false),
            ])
            ->openUrlInNewTab()
            ->action(function (Product $record, array $data) {
                return redirect()->away(route('pdf.guarantee', [
                    'id' => $record->id,
                    'lang' => $data['lang'],
                ]));
            })
            ->visible(fn () => static::canAbility('CanViewPdf'));
    }

    /**
     * Preview Gallery (modal)
     */
    protected static function previewGalleryAction(): Action
    {
        return Action::make('previewGallery')
            ->label(__('admin.preview'))
            ->icon(Heroicon::Photo)
            ->color(fn(Product $record) => !isset($record->images[0]) ? 'danger' : 'success')
            ->modalHeading(__('admin.preview'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('admin.close'))
            ->closeModalByClickingAway(true)
            ->disabled(fn(Product $record) => !isset($record->images[0]))
            ->modalContent(function (Product $record) {
                $images = is_array($record->images) ? array_values(array_filter($record->images)) : [];

                return view('filament.modals.product-gallery', [
                    'images' => $images,
                    'title' => $record->sku ?? $record->name ?? 'Gallery',
                ]);
            });
    }

    /**
     * All actions in one place
     */
    protected static function productActions(): array
    {
        return [
            ViewAction::make()
                ->modal()
                ->label(__('admin.service_comment')),
            EditAction::make(),
            static::warrantyPdfAction(),
            static::previewGalleryAction(),
        ];
    }
}
