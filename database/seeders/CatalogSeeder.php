<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SizeChart;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Football', 'name_bn' => 'ফুটবল', 'slug' => 'football'],
            ['name' => 'Cricket', 'name_bn' => 'ক্রিকেট', 'slug' => 'cricket'],
            ['name' => 'Polo / T-shirt', 'name_bn' => 'পোলো / টি-শার্ট', 'slug' => 'polo-t-shirt'],
            ['name' => 'Training / Sports', 'name_bn' => 'ট্রেনিং / স্পোর্টস', 'slug' => 'training-sports'],
            ['name' => 'Winter / Hoodie', 'name_bn' => 'শীতের পোশাক / হুডি', 'slug' => 'winter-hoodie'],
            ['name' => 'Limited Edition', 'name_bn' => 'লিমিটেড এডিশন', 'slug' => 'limited-edition'],
            ['name' => 'Alumni', 'name_bn' => 'প্রাক্তন শিক্ষার্থী', 'slug' => 'alumni'],
            ['name' => 'Fan Wear', 'name_bn' => 'ফ্যান ওয়্যার', 'slug' => 'fan-wear'],
        ];

        foreach ($categories as $attributes) {
            Category::updateOrCreate(['slug' => $attributes['slug']], $attributes + ['is_active' => true]);
        }

        $collections = [
            ['name' => '23rd Batch', 'name_bn' => '২৩তম ব্যাচ', 'slug' => '23rd-batch', 'type' => 'batch'],
            ['name' => 'Computer Science', 'name_bn' => 'কম্পিউটার সায়েন্স', 'slug' => 'computer-science', 'type' => 'department'],
        ];

        foreach ($collections as $attributes) {
            Collection::updateOrCreate(['slug' => $attributes['slug']], $attributes + ['is_active' => true]);
        }

        $products = [
            [
                'category_slug' => 'football',
                'name' => 'Campus Football Jersey',
                'name_bn' => 'ক্যাম্পাস ফুটবল জার্সি',
                'slug' => 'campus-football-jersey',
                'sku' => 'DHJ-FOOT-001',
                'fabric_type' => 'Premium dry-fit polyester',
                'gsm' => 160,
                'fit' => 'regular',
                'collar_type' => 'round',
                'sleeve_type' => 'short',
                'price' => 850,
                'compare_at_price' => 1000,
            ],
            [
                'category_slug' => 'polo-t-shirt',
                'name' => 'Campus Polo Shirt',
                'name_bn' => 'ক্যাম্পাস পোলো শার্ট',
                'slug' => 'campus-polo-shirt',
                'sku' => 'DHJ-POLO-001',
                'fabric_type' => 'Cotton pique',
                'gsm' => 220,
                'fit' => 'regular',
                'collar_type' => 'polo',
                'sleeve_type' => 'short',
                'price' => 950,
                'compare_at_price' => null,
            ],
        ];

        foreach ($products as $attributes) {
            $category = Category::where('slug', $attributes['category_slug'])->firstOrFail();
            unset($attributes['category_slug']);

            $product = Product::updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes + [
                    'category_id' => $category->id,
                    'description' => 'Comfortable campus wear designed for everyday use.',
                    'is_active' => true,
                ],
            );

            $product->collections()->syncWithoutDetaching([
                Collection::where('slug', '23rd-batch')->value('id'),
                Collection::where('slug', 'computer-science')->value('id'),
            ]);

            foreach (['S' => [36, 27, 16], 'M' => [38, 28, 17], 'L' => [40, 29, 18], 'XL' => [42, 30, 19], 'XXL' => [44, 31, 20]] as $size => [$chest, $length, $shoulder]) {
                SizeChart::updateOrCreate(
                    ['product_id' => $product->id, 'size' => $size],
                    [
                        'chest_inches' => $chest,
                        'length_inches' => $length,
                        'shoulder_inches' => $shoulder,
                        'chest_cm' => round($chest * 2.54, 2),
                        'length_cm' => round($length * 2.54, 2),
                        'shoulder_cm' => round($shoulder * 2.54, 2),
                    ],
                );

                foreach (['Black' => '#111827', 'White' => '#ffffff'] as $color => $colorCode) {
                    ProductVariant::firstOrCreate(
                        ['product_id' => $product->id, 'size' => $size, 'color' => $color],
                        [
                            'sku' => $product->sku.'-'.$size.'-'.strtoupper($color),
                            'color_code' => $colorCode,
                            'stock_quantity' => 20,
                            'low_stock_threshold' => 5,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }
    }
}
