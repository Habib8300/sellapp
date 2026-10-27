<?php

namespace Database\Seeders;

use App\Models\HomeHeroSlide;
use Illuminate\Database\Seeder;

class HomeHeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        HomeHeroSlide::firstOrCreate(
            ['title' => implode("\n", ['আপনার ক্যাম্পাস।', 'আপনার পরিচয়।', 'আপনার জার্সি।'])],
            [
                'subtitle' => 'আপনার ব্যাচ, দল আর ক্যাম্পাসের গল্প বলুক আপনার জার্সি। পছন্দের ডিজাইন খুঁজে নিন—নিজের রঙে, নিজের পরিচয়ে।',
                'button_text' => 'জার্সি দেখুন',
                'button_url' => '/products',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );
    }
}
