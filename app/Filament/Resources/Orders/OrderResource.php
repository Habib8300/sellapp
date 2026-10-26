<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Jobs\SendOrderStatusEmail;
use App\Jobs\SendOrderStatusSms;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|\UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('order_number')->searchable()->copyable(),
            TextColumn::make('customer_name')->searchable(),
            TextColumn::make('customer_phone')->searchable(),
            TextColumn::make('total')->money('BDT')->sortable(),
            TextColumn::make('payment_status')->badge(),
            SelectColumn::make('status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'in_production' => 'In production',
                'ready' => 'Ready',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
                'returned' => 'Returned',
            ])->afterStateUpdated(function (Order $record, string $state): void {
                $record->statusHistory()->create([
                    'user_id' => auth()->id(),
                    'status' => $state,
                    'note' => 'Order status updated in admin panel.',
                ]);
                SendOrderStatusEmail::dispatch($record->id)->afterCommit();
                SendOrderStatusSms::dispatch($record->id)->afterCommit();
            }),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'in_production' => 'In production',
                'ready' => 'Ready',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
                'returned' => 'Returned',
            ]),
        ])->headerActions([
            Action::make('export')
                ->label('Export orders CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): StreamedResponse {
                    $orders = Order::query()->with('items')->latest()->limit(5000)->get();

                    return response()->streamDownload(function () use ($orders): void {
                        $output = fopen('php://output', 'w');
                        if ($output === false) {
                            throw new \RuntimeException('Unable to open report export stream.');
                        }
                        fwrite($output, "\xEF\xBB\xBF");
                        fputcsv($output, ['Order', 'Customer', 'Phone', 'Date', 'Status', 'Payment status', 'Product', 'SKU', 'Size', 'Color', 'Quantity', 'Unit price', 'Line total']);
                        foreach ($orders as $order) {
                            foreach ($order->items as $item) {
                                fputcsv($output, [
                                    $order->order_number, $order->customer_name, $order->customer_phone,
                                    $order->created_at->toDateTimeString(), $order->status, $order->payment_status,
                                    $item->product_name, $item->product_sku, $item->size, $item->color,
                                    $item->quantity, $item->unit_price, $item->line_total,
                                ]);
                            }
                        }
                        fclose($output);
                    }, 'orders-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
                }),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('items');
    }

    public static function getPages(): array
    {
        return ['index' => ManageOrders::route('/')];
    }
}
