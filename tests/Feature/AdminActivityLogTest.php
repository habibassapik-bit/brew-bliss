<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $superAdmin;
    }

    public function test_guest_and_customer_cannot_access_activity_log(): void
    {
        $customer = User::factory()->create();

        $this->get(route('admin.activity-logs.index'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.activity-logs.index'))->assertForbidden();
    }

    public function test_product_creation_is_logged(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $category = Category::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.products.store'), [
            'name' => 'Risol Uji',
            'category_id' => $category->id,
            'price' => 10000,
            'cost_price' => 4000,
            'stock' => 10,
            'status' => 'available',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'product.created',
            'subject_type' => Product::class,
        ]);
    }

    public function test_order_status_change_is_logged(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $order = Order::factory()->paid()->create();

        $this->actingAs($superAdmin)->patch(route('admin.orders.status', $order), [
            'status' => OrderStatus::Processing->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'order.status_changed',
            'subject_id' => $order->id,
        ]);
    }

    public function test_payment_verification_is_logged(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $order = Order::factory()->create();
        $payment = Payment::factory()->create(['order_id' => $order->id]);

        $this->actingAs($superAdmin)->post(route('admin.payments.approve', $payment))->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'payment.approved',
            'subject_id' => $payment->id,
        ]);
    }

    public function test_operational_cost_management_is_logged(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)->post(route('admin.costs.store'), [
            'name' => 'Gas dapur',
            'category' => 'operational',
            'amount' => '100000',
            'incurred_at' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'cost.created',
        ]);
    }

    public function test_log_page_shows_admin_name_and_time(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $category = Category::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.products.store'), [
            'name' => 'Risol Uji',
            'category_id' => $category->id,
            'price' => 10000,
            'cost_price' => 4000,
            'stock' => 10,
            'status' => 'available',
        ])->assertRedirect();

        $response = $this->actingAs($superAdmin)->get(route('admin.activity-logs.index'));

        $response->assertOk();
        $response->assertSee($superAdmin->name);
        $response->assertSee('Produk dibuat');
        $response->assertSee('Risol Uji');
        $response->assertSee('WIB');
    }

    public function test_log_page_is_sorted_latest_first(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $category = Category::factory()->create();

        $this->actingAs($superAdmin)->post(route('admin.categories.store'), ['name' => 'Kategori Awal'])->assertRedirect();

        $this->actingAs($superAdmin)->post(route('admin.products.store'), [
            'name' => 'Produk Terakhir',
            'category_id' => $category->id,
            'price' => 10000,
            'cost_price' => 4000,
            'stock' => 10,
            'status' => 'available',
        ])->assertRedirect();

        $response = $this->actingAs($superAdmin)->get(route('admin.activity-logs.index'));

        $response->assertOk();

        $content = (string) $response->getContent();
        $productIndex = mb_strpos($content, 'Produk Terakhir');
        $categoryIndex = mb_strpos($content, 'Kategori Awal');

        $this->assertNotFalse($productIndex);
        $this->assertNotFalse($categoryIndex);
        $this->assertLessThan($categoryIndex, $productIndex);
    }

    public function test_logger_skips_anonymous_context_without_error(): void
    {
        $result = ActivityLogger::log('order.cancelled', null, ['description' => 'Seharusnya tidak tersimpan.']);

        $this->assertNull($result);
        $this->assertDatabaseCount('activity_logs', 0);
    }
}
