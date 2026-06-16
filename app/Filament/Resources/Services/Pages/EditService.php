<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Livewire\Attributes\On;

class EditService extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ServiceResource::class;

    protected Width | string | null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    #[On('refreshService')]
    public function refresh(): void
    {
        $this->js('window.location.reload()');
    }
}
