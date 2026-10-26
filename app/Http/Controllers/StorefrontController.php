<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('storefront', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(8)
                ->get(),
            'collections' => Collection::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(6)
                ->get(),
            'products' => $this->publishedProducts()
                ->with(['category', 'images', 'variants'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'collection' => ['nullable', 'string', 'exists:collections,slug'],
            'size' => ['nullable', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'max:60'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'availability' => ['nullable', 'in:in_stock,preorder'],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc'],
        ]);

        $query = $this->publishedProducts()
            ->with(['category', 'images', 'variants']);

        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $query->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('name_bn', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhereHas('category', fn (Builder $category) => $category
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('name_bn', 'like', "%{$term}%"));
            });
        }

        if (filled($filters['category'] ?? null)) {
            $query->whereHas('category', fn (Builder $category) => $category->where('slug', $filters['category']));
        }

        if (filled($filters['collection'] ?? null)) {
            $query->whereHas('collections', fn (Builder $collection) => $collection->where('slug', $filters['collection']));
        }

        if (filled($filters['size'] ?? null) || filled($filters['color'] ?? null)) {
            $query->whereHas('variants', function (Builder $variant) use ($filters): void {
                $variant->where('is_active', true)
                    ->when(filled($filters['size'] ?? null), fn (Builder $variant) => $variant->where('size', $filters['size']))
                    ->when(filled($filters['color'] ?? null), fn (Builder $variant) => $variant->where('color', $filters['color']));
            });
        }

        $query->when(isset($filters['min_price']), fn (Builder $query) => $query->where('price', '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn (Builder $query) => $query->where('price', '<=', $filters['max_price']));

        if (($filters['availability'] ?? null) === 'in_stock') {
            $query->whereHas('variants', fn (Builder $variant) => $variant
                ->where('is_active', true)
                ->where('stock_quantity', '>', 0));
        } elseif (($filters['availability'] ?? null) === 'preorder') {
            $query->where('is_preorder', true)
                ->where(fn (Builder $query) => $query
                    ->whereNull('preorder_close_at')
                    ->orWhereDate('preorder_close_at', '>=', today()));
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        return view('catalog.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'collections' => Collection::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(string $slug): View
    {
        $product = $this->publishedProducts()
            ->with(['category', 'collections', 'images', 'variants', 'sizeCharts'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = $this->publishedProducts()
            ->with(['category', 'images', 'variants'])
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->latest()
            ->limit(4)
            ->get();

        $customizationPrintCharge = (float) (Setting::query()->where('key', 'customization_print_charge')->value('value') ?: 150);

        return view('catalog.show', compact('product', 'related', 'customizationPrintCharge'));
    }

    private function publishedProducts(): Builder
    {
        return Product::query()
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true));
    }
}
