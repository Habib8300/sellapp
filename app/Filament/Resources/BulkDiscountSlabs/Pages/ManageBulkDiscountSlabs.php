<?php

namespace App\Filament\Resources\BulkDiscountSlabs\Pages;

use App\Filament\Resources\BulkDiscountSlabs\BulkDiscountSlabResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBulkDiscountSlabs extends ManageRecords
{
    protected static string $resource = BulkDiscountSlabResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
