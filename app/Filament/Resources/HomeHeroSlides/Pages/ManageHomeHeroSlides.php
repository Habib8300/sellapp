<?php

namespace App\Filament\Resources\HomeHeroSlides\Pages;

use App\Filament\Resources\HomeHeroSlides\HomeHeroSlideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHomeHeroSlides extends ManageRecords
{
    protected static string $resource = HomeHeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
