<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    public function run(): void
    {
        CmsPage::firstOrCreate(
            ['slug' => 'return-policy'],
            [
                'title' => 'রিটার্ন ও সাইজ এক্সচেঞ্জ নীতি',
                'body' => 'ডেলিভারি সম্পন্ন হওয়ার ৭ দিনের মধ্যে অর্ডার করা পণ্যের জন্য এক্সচেঞ্জ বা রিটার্ন অনুরোধ জমা দিন। আমার অ্যাকাউন্ট পৃষ্ঠা থেকে অর্ডারের আইটেম নির্বাচন করে কারণ ও অনুরোধের ধরন পাঠানো যাবে। আমাদের টিম অনুরোধটি পর্যালোচনা করে পরবর্তী ধাপ, পণ্যের প্রাপ্যতা এবং প্রযোজ্য ডেলিভারি ব্যবস্থার বিষয়ে যোগাযোগ করবে। অনুরোধ জমা দেওয়া মানেই অনুমোদন নয়।',
                'meta_title' => 'রিটার্ন ও সাইজ এক্সচেঞ্জ নীতি',
                'meta_description' => 'Dhakebir Jersey House-এর অর্ডারের রিটার্ন ও সাইজ এক্সচেঞ্জের নিয়ম দেখুন।',
                'is_published' => true,
            ],
        );
    }
}
