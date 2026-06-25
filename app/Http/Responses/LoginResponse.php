<?php

namespace App\Http\Responses;

use App\Filament\Resources\Products\ProductResource;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends \Filament\Auth\Http\Responses\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->to(ProductResource::getUrl('index'));
    }
}