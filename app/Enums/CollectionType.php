<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CollectionType: string implements HasLabel
{
    case Batch = 'batch';
    case Department = 'department';
    case Club = 'club';
    case Event = 'event';
    case Season = 'season';
    case Tournament = 'tournament';

    public function getLabel(): string
    {
        return match ($this) {
            self::Batch => 'Batch',
            self::Department => 'Department',
            self::Club => 'Club',
            self::Event => 'Event',
            self::Season => 'Season',
            self::Tournament => 'Tournament',
        };
    }
}
