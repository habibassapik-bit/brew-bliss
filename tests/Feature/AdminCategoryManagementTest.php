<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $user;
    }

    public function test_guest_is_redirected_from_admin_categories(): void
    {
        $this->get('/admin/kategori')->assertRedirect('/masuk');
        $this->get('/admin/kategori/baru')->assertRedirect('/masuk');
    }

    public function test_customer_cannot_access_admin_categories(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin/kategori');

        $response->assertForbidden();
    }

    public function test_admin_cs_cannot_access_admin_categories(): void
    {
        $adminCs = User::factory()->create();
        $adminCs->assignRole(Role::create(['name' => 'admin_cs']));

        $response = $this->actingAs($adminCs)->get('/admin/kategori');

        $response->assertForbidden();
    }

    public function test_super_admin_can_view_categories_index(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin())->get('/admin/kategori');

        $response->assertOk();
        $response->assertSee($category->name);
    }

    public function test_super_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->superAdmin())->post('/admin/kategori', [
            'name' => 'Snack Tradisional',
            'sort_order' => 5,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');

        $category = Category::query()->where('name', 'Snack Tradisional')->first();
        $this->assertNotNull($category);
        $this->assertSame('snack-tradisional', $category->slug);
        $this->assertSame(5, $category->sort_order);
        $this->assertTrue($category->is_active);
    }

    public function test_category_slug_is_made_unique_on_create(): void
    {
        Category::factory()->create(['name' => 'Minuman', 'slug' => 'minuman']);

        $this->actingAs($this->superAdmin())->post('/admin/kategori', [
            'name' => 'Minuman',
        ]);

        $this->assertSame(2, Category::query()->where('slug', 'like', 'minuman%')->count());
    }

    public function test_create_category_validation_fails(): void
    {
        $response = $this->actingAs($this->superAdmin())->post('/admin/kategori', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertSame(0, Category::query()->count());
    }

    public function test_super_admin_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Lama', 'slug' => 'lama']);

        $response = $this->actingAs($this->superAdmin())->put("/admin/kategori/{$category->id}", [
            'name' => 'Baru',
            'sort_order' => 3,
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('Baru', $category->name);
        $this->assertSame('baru', $category->slug);
        $this->assertSame(3, $category->sort_order);
        $this->assertFalse($category->is_active);
    }

    public function test_deactivated_category_hides_its_products_from_customer_catalog(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Risol Spesial']);

        $this->get('/produk?category='.$category->slug)
            ->assertOk()
            ->assertSee('Risol Spesial');

        $this->actingAs($this->superAdmin())->put("/admin/kategori/{$category->id}", [
            'name' => $category->name,
            'is_active' => '0',
        ]);

        $this->get('/produk?category='.$category->slug)
            ->assertOk()
            ->assertDontSee('Risol Spesial');
    }

    public function test_super_admin_can_delete_unused_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin())->delete("/admin/kategori/{$category->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_only_soft_deleted_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id])->delete();

        $response = $this->actingAs($this->superAdmin())->delete("/admin/kategori/{$category->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->superAdmin())->delete("/admin/kategori/{$category->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
