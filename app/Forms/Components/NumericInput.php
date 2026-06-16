<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

class NumericInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->type('decimal')
            ->default(0)
            ->live()
            ->minValue(0)
            ->numeric()
            ->inputMode('decimal')
            ->extraInputAttributes([
                'x-on:input.capture' => <<<'JS'
                     $event.target.value = ($event.target.value ?? '')
                    .replace(/,/g, '.')
                    .replace(/\s+/g, '');
                JS,
            ]);
    }
}
