<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'posts' => BlogPost::query()
                ->where('is_published', true)
                ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->paginate(12),
        ]);
    }

    public function blogPost(string $slug): View
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        return view('blog.show', compact('post'));
    }

    public function health(): Response
    {
        $checks = [
            'app' => true,
            'database' => true,
            'cache' => true,
            'storage' => true,
        ];

        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $checks['database'] = false;
        }

        try {
            Cache::put('health-check', now()->toIso8601String(), 60);
            Cache::get('health-check');
        } catch (\Throwable) {
            $checks['cache'] = false;
        }

        try {
            Storage::disk('local')->put('health-check.txt', 'ok');
            Storage::disk('local')->delete('health-check.txt');
        } catch (\Throwable) {
            $checks['storage'] = false;
        }

        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'app_version' => config('app.version', 'dev'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
        ], $healthy ? 200 : 503);
    }

    public function robots(): Response
    {
        return response(
            "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /login\nDisallow: /register\nDisallow: /profile\nDisallow: /dashboard\n\nSitemap: ".url('/sitemap.xml')."\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    public function sitemap(): Response
    {
        return response()->view('seo.sitemap', [
            'products' => Product::query()->where('is_active', true)->latest('updated_at')->get(['slug', 'updated_at']),
            'pages' => CmsPage::query()->where('is_published', true)->latest('updated_at')->get(['slug', 'updated_at']),
            'posts' => BlogPost::query()
                ->where('is_published', true)
                ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->latest('updated_at')
                ->get(['slug', 'updated_at']),
        ])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
