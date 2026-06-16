<?php

namespace App\Observers;

use App\Models\AccessoryOrderItem;
use Filament\Notifications\Notification;
use Exception;
use Illuminate\Validation\ValidationException;

class AccessoryOrderItemObserver
{
    /**
     * Handle the AccessoryOrderItem "creating" event.
     * @throws Exception
     */
    public function saving(AccessoryOrderItem $item): void
    {
        $accessory = $item->accessory;
        if ($accessory && $accessory->quantity < $item->quantity) {
            Notification::make()
                ->title('მარაგი ამოწურულია')
                ->danger()
                ->body("მარაგი ამოწურულია: {$accessory->name}")
                ->send();

            throw ValidationException::withMessages([
                'items.*.quantity' => ["მარაგი ამოწურულია: {$accessory->name}"],
            ]);
        }
    }

    /**
     * Handle the AccessoryOrderItem "created" event.
     */
    public function created(AccessoryOrderItem $item): void
    {
        $accessory = $item->accessory;
        if ($accessory) {
            $accessory->quantity = max(0, $accessory->quantity - $item->quantity);
            $accessory->save();
        }
    }
}
