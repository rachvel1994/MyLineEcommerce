<?php

namespace App\Filament\Resources\Deliveries\Pages;

use App\Filament\Resources\Deliveries\DeliveryResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = DeliveryResource::class;
}
