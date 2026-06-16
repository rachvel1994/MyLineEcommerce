<?php

namespace App\Filament\Traits;

trait RedirectsBackToPreviousResourcePage
{
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}