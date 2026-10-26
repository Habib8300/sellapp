<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductFit: string implements HasLabel
{
    case Regular = 'regular';
    case Slim = 'slim';

    public function getLabel(): string
    {
        return match ($this) {
            self::Regular => 'Regular',
            self::Slim => 'Slim',
        };
    }
}
