<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

class PriceInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->type('text')
            ->default(0)
            ->postfix('₾')
            ->required()
            ->numeric()
            ->inputMode('decimal')
            ->live()
            ->extraInputAttributes([
                'x-on:input.capture' => <<<'JS'
                     $event.target.value = ($event.target.value ?? '')
                    .replace(/,/g, '.')
                    .replace(/\s+/g, '');
                JS,
            ]);
    }
}
