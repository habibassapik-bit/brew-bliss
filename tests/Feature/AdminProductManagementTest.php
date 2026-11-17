<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(Category $category, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Risol Mayo',
            'category_id' => $category->id,
            'description' => 'Risol isi mayo',
            'price' => 15000,
            'cost_price' => 9000,
            'stock' => 25,
            'status' => ProductStatus::Available->value,
        ], $overrides);
    }

    public function test_guest_is_redirected_from_admin_products(): void
    {
        $this->get('/admin/produk')->assertRedirect('/masuk');
        $this->get('/admin/produk/baru')->assertRedirect('/masuk');
    }

    public function test_customer_cannot_access_admin_products(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin/produk');

        $response->assertForbidden();
    }

    public function test_admin_cs_cannot_access_admin_products(): void
    {
        $adminCs = User::factory()->create();
        $adminCs->assignRole(Role::create(['name' => 'admin_cs']));

        $response = $this->actingAs($adminCs)->get('/admin/produk');

        $response->assertForbidden();
    }

    public function test_super_admin_can_view_products_index(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->superAdmin())->get('/admin/produk');

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_super_admin_can_filter_products_by_search_and_status(): void
    {
        $product = Product::factory()->create(['name' => 'Risol Spesial']);
        Product::factory()->create(['name' => 'Pisang Goreng', 'status' => ProductStatus::SoldOut, 'stock' => 0]);

        $this->actingAs($this->superAdmin())->get('/admin/produk?q=Risol')
            ->assertOk()
            ->assertSee('Risol Spesial')
            ->assertDontSee('Pisang Goreng');

        $this->actingAs($this->superAdmin())->get('/admin/produk?status=sold_out')
            ->assertOk()
            ->assertSee('Pisang Goreng')
            ->assertDontSee('Risol Spesial');
    }

    public function test_super_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post('/admin/produk', $this->validPayload($category));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('status');

        $product = Product::query()->where('name', 'Risol Mayo')->first();
        $this->assertNotNull($product);
        $this->assertSame('risol-mayo', $product->slug);
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame(15000.0, (float) $product->price);
        $this->assertSame(9000.0, (float) $product->cost_price);
        $this->assertSame(25, $product->stock);
        $this->assertSame(ProductStatus::Available, $product->status);
    }

    public function test_created_product_appears_in_customer_catalog(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->superAdmin())->post('/admin/produk', $this->validPayload($category));

        $product = Product::query()->where('name', 'Risol Mayo')->firstOrFail();

        $this->get('/produk')->assertOk()->assertSee('Risol Mayo');
        $this->get("/produk/{$product->slug}")->assertOk()->assertSee('Risol Mayo');
    }

    public function test_create_product_validation_fails(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post('/admin/produk', $this->validPayload($category, [
            'price' => -5,
            'stock' => -1,
            'status' => 'kaput',
        ]));

        $response->assertSessionHasErrors(['price', 'stock', 'status']);
        $this->assertSame(0, Product::query()->count());
    }

    public function test_super_admin_can_create_product_with_image(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs($this->superAdmin())->post('/admin/produk', $this->validPayload($category, [
            'image' => UploadedFile::fake()->image('risol.jpg', 100, 100),
        ]));

        $product = Product::query()->where('name', 'Risol Mayo')->firstOrFail();
        Storage::disk('public')->assertExists($product->image);
        $this->assertStringStartsWith('products/', $product->image);
    }

    public function test_image_upload_rejects_invalid_file(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post('/admin/produk', $this->validPayload($category, [
            'image' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ]));

        $response->assertSessionHasErrors(['image']);
        $this->assertSame(0, Product::query()->count());
    }

    public function test_super_admin_can_update_product(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Risol Lama']);

        $response = $this->actingAs($this->superAdmin())->put("/admin/produk/{$product->id}", $this->validPayload($category, [
            'name' => 'Risol Baru',
            'price' => 18000,
            'cost_price' => 10000,
            'stock' => 40,
            'status' => ProductStatus::SoldOut->value,
            'image' => UploadedFile::fake()->image('baru.jpg', 100, 100),
        ]));

        $response->assertRedirect(route('admin.products.show', $product));

        $product->refresh();
        $this->assertSame('Risol Baru', $product->name);
        $this->assertSame('risol-baru', $product->slug);
        $this->assertSame(18000.0, (float) $product->price);
        $this->assertSame(10000.0, (float) $product->cost_price);
        $this->assertSame(40, $product->stock);
        $this->assertSame(ProductStatus::SoldOut, $product->status);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_replacing_image_deletes_old_file(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $oldImage = UploadedFile::fake()->image('lama.jpg', 100, 100)->store('products', 'public');
        $product->update(['image' => $oldImage]);

        $this->actingAs($this->superAdmin())->put("/admin/produk/{$product->id}", $this->validPayload($category, [
            'name' => $product->name,
            'image' => UploadedFile::fake()->image('baru.jpg', 100, 100),
        ]));

        Storage::disk('public')->assertMissing($oldImage);
        $this->assertNotSame($oldImage, $product->fresh()->image);
    }

    public function test_selling_out_product_shows_habis_in_catalog(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Risol Habis']);

        $this->get("/produk/{$product->slug}")->assertOk();

        $this->actingAs($this->superAdmin())->put("/admin/produk/{$product->id}", $this->validPayload($category, [
            'name' => $product->name,
            'status' => ProductStatus::SoldOut->value,
            'stock' => 0,
        ]));

        $this->get('/produk?availability=habis')->assertOk()->assertSee('Risol Habis');
    }

    public function test_inactive_product_is_hidden_and_not_orderable(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Risol Rahasia']);
        $customer = User::factory()->create();

        $this->get('/produk')->assertOk()->assertSee('Risol Rahasia');

        $this->actingAs($this->superAdmin())->put("/admin/produk/{$product->id}", $this->validPayload($category, [
            'name' => $product->name,
            'status' => ProductStatus::Inactive->value,
        ]));

        $this->get('/produk')->assertOk()->assertDontSee($product->slug);
        $this->get("/produk/{$product->slug}")->assertNotFound();

        $response = $this->actingAs($customer)->post('/keranjang/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['product_id']);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_super_admin_can_soft_delete_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Risol Dihapus']);

        $response = $this->actingAs($this->superAdmin())->delete("/admin/produk/{$product->id}");

        $response->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted($product);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->get('/produk')->assertOk()->assertDontSee($product->slug);
        $this->get("/produk/{$product->slug}")->assertNotFound();
    }

    public function test_soft_deleted_product_keeps_transaction_history(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->superAdmin())->delete("/admin/produk/{$product->id}");

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertSame(1, Product::withTrashed()->where('id', $product->id)->count());
    }
}
