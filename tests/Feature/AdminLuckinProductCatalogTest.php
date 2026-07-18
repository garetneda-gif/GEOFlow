<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLuckinProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_requires_an_authenticated_admin(): void
    {
        $this->get(route('admin.knowledge-bases.luckin-products.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_browse_the_official_product_visual_catalog(): void
    {
        $admin = Admin::query()->create([
            'username' => 'catalog-admin',
            'password' => 'secret-123',
            'email' => 'catalog@example.com',
            'display_name' => 'Catalog Admin',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.knowledge-bases.luckin-products.index'));

        $response
            ->assertOk()
            ->assertSee('瑞幸官网产品视觉库')
            ->assertSee('生椰拿铁')
            ->assertSee('牛油果羽衣酸奶昔（仔仔杯）')
            ->assertSee('拿铁类')
            ->assertSee('其他非咖啡类')
            ->assertSee('2026-07-18')
            ->assertSee('https://lkcoffee.com/products', false)
            ->assertSee('/images/luckin-products/1.png', false)
            ->assertSee('data-product-search', false)
            ->assertSee('data-category="2"', false);

        $this->assertSame(33, substr_count($response->getContent(), 'data-product-item'));
    }

    public function test_knowledge_base_index_links_to_the_catalog(): void
    {
        $admin = Admin::query()->create([
            'username' => 'index-admin',
            'password' => 'secret-123',
            'email' => 'index@example.com',
            'display_name' => 'Index Admin',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.knowledge-bases.index'))
            ->assertOk()
            ->assertSee('瑞幸官网产品视觉库')
            ->assertSee(route('admin.knowledge-bases.luckin-products.index'), false);
    }

    public function test_every_snapshot_product_uses_a_local_official_image(): void
    {
        $products = json_decode(
            (string) file_get_contents(resource_path('data/luckin-products.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertCount(33, $products);
        $this->assertCount(33, array_unique(array_column($products, 'id')));

        foreach ($products as $product) {
            $this->assertStringStartsWith('https://img.luckincoffeecdn.com/', $product['source_image_url']);
            $this->assertStringStartsWith('https://lkcoffee.com/products/', $product['official_url']);
            $this->assertFileExists(public_path(ltrim($product['image'], '/')));
        }
    }
}
