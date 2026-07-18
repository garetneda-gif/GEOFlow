<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminWeb;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use JsonException;

final class LuckinProductCatalogController extends Controller
{
    /**
     * @throws JsonException
     */
    public function index(): View
    {
        $products = collect(json_decode(
            (string) file_get_contents(resource_path('data/luckin-products.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        ));

        return view('admin.knowledge-bases.luckin-products', [
            'pageTitle' => '瑞幸官网产品视觉库',
            'activeMenu' => 'materials',
            'adminSiteName' => AdminWeb::siteName(),
            'products' => $products,
            'categories' => $this->categories($products),
            'snapshotDate' => '2026-07-18',
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     * @return Collection<int, array{id: int, name: string, count: int}>
     */
    private function categories(Collection $products): Collection
    {
        return $products
            ->groupBy('category_id')
            ->map(fn (Collection $items, int|string $categoryId): array => [
                'id' => (int) $categoryId,
                'name' => (string) $items->first()['category'],
                'count' => $items->count(),
            ])
            ->values();
    }
}
