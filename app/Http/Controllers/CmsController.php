<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\Product;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CmsController extends Controller
{
    public function page(string $slug): View
    {
        $page = CmsPage::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('cms.page', compact('page'));
    }

    public function blogIndex(): View
    {
        return view('blog.index', [
            'posts' => BlogPost::query()->where('is_published', true)->where('published_at', '<=', now())
                ->latest('published_at')->paginate(12),
        ]);
    }

    public function blogPost(string $slug): View
    {
        $post = BlogPost::query()->where('slug', $slug)->where('is_published', true)
            ->where('published_at', '<=', now())->firstOrFail();

        return view('blog.show', compact('post'));
    }

    public function sitemap(): Response
    {
        return response()->view('seo.sitemap', [
            'products' => Product::query()->where('is_active', true)->latest('updated_at')->get(['slug', 'updated_at']),
            'pages' => CmsPage::query()->where('is_published', true)->latest('updated_at')->get(['slug', 'updated_at']),
            'posts' => BlogPost::query()->where('is_published', true)->where('published_at', '<=', now())->latest('updated_at')->get(['slug', 'updated_at']),
        ])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
