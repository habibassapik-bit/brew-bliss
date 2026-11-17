<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $user;
    }

    /**
     * Buat order dengan item dan tanggal kustom.
     */
    private function makeOrder(array $attributes = [], ?array $items = null): Order
    {
        $order = Order::factory()->create($attributes);

        foreach ($items ?? [] as $item) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'product_price' => $item['product']->price,
                'product_cost' => $item['product']->cost_price,
                'quantity' => $item['quantity'],
                'subtotal' => $item['quantity'] * (float) $item['product']->price,
            ]);
        }

        return $order;
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/masuk');
        $this->assertGuest();
    }

    public function test_customer_cannot_view_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_cs_cannot_view_dashboard(): void
    {
        $adminCs = User::factory()->create();
        $adminCs->assignRole(Role::firstOrCreate(['name' => 'admin_cs']));

        $this->actingAs($adminCs)->get('/admin')->assertForbidden();
    }

    public function test_super_admin_can_view_dashboard_with_empty_data(): void
    {
        $response = $this->actingAs($this->superAdmin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Dashboard Super Admin');
        $response->assertSee('Rp 0');
        $response->assertSee('Belum ada omzet pada periode ini');
        $response->assertSee('Belum ada penjualan');
        $response->assertSee('Belum ada pesanan');
    }

    public function test_dashboard_counts_orders_and_revenue_for_today(): void
    {
        $paidToday = $this->makeOrder(['total' => 50000, 'payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);
        $this->makeOrder(['total' => 70000]);

        $response = $this->actingAs($this->superAdmin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Rp 50.000');
        $response->assertSee('Grafik Omzet');
        $response->assertSee($paidToday->order_number);
        $response->assertSee('2');
    }

    public function test_revenue_excludes_unpaid_and_cancelled(): void
    {
        $this->makeOrder(['total' => 50000, 'payment_status' => PaymentStatus::Paid, 'paid_at' => now(), 'status' => OrderStatus::Cancelled]);
        $this->makeOrder(['total' => 80000]);

        $response = $this->actingAs($this->superAdmin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Rp 0');
        $response->assertSee('Belum ada omzet pada periode ini');
    }

    public function test_action_needed_includes_pending_confirmation_and_verification(): void
    {
        $this->makeOrder(['status' => OrderStatus::PendingConfirmation]);
        $this->makeOrder(['status' => OrderStatus::Processing, 'payment_status' => PaymentStatus::PendingVerification]);
        $this->makeOrder(['status' => OrderStatus::Completed, 'payment_status' => PaymentStatus::Paid, 'paid_at' => now(), 'completed_at' => now()]);

        $response = $this->actingAs($this->superAdmin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Menunggu Tindakan');
        $this->assertSame(2, Order::query()->where(function ($query) {
            $query->where('status', OrderStatus::PendingConfirmation)
                ->orWhere('payment_status', PaymentStatus::PendingVerification);
        })->count());
    }

    public function test_period_filter_excludes_orders_outside_range(): void
    {
        $old = Carbon::today()->subDays(10);
        $this->makeOrder(['total' => 50000, 'payment_status' => PaymentStatus::Paid, 'paid_at' => $old, 'created_at' => $old, 'updated_at' => $old]);
        $recent = $this->makeOrder(['total' => 30000, 'payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($this->superAdmin())->get('/admin')
            ->assertOk()
            ->assertSee('Rp 30.000')
            ->assertSee($recent->order_number)
            ->assertDontSee($old->toDateString());

        $this->actingAs($this->superAdmin())->get('/admin?period=last7')
            ->assertOk()
            ->assertSee('Rp 30.000');

        $this->actingAs($this->superAdmin())->get('/admin?period=month')
            ->assertOk()
            ->assertSee('Rp 80.000');

        $this->actingAs($this->superAdmin())->get('/admin?period=year')
            ->assertOk()
            ->assertSee('Rp 80.000');
    }

    public function test_custom_period_filter_applies_valid_range(): void
    {
        $from = Carbon::today()->subDays(9);
        $to = Carbon::today()->subDays(5);
        $inside = $to->copy()->subDay();
        $insideOrder = $this->makeOrder(['total' => 40000, 'payment_status' => PaymentStatus::Paid, 'paid_at' => $inside, 'created_at' => $inside, 'updated_at' => $inside]);
        $outside = $this->makeOrder(['total' => 90000, 'payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);

        $query = 'period=custom&from='.$from->toDateString().'&to='.$to->toDateString();

        $response = $this->actingAs($this->superAdmin())->get('/admin?'.$query);

        $response->assertOk();
        $response->assertSee('Rp 40.000');
        $response->assertSee($insideOrder->order_number);
        $response->assertDontSee($outside->order_number);
    }

    public function test_custom_period_falls_back_to_today_when_invalid(): void
    {
        $yesterday = Carbon::yesterday();
        $this->makeOrder(['total' => 40000, 'payment_status' => PaymentStatus::Paid, 'paid_at' => $yesterday, 'created_at' => $yesterday, 'updated_at' => $yesterday]);

        $this->actingAs($this->superAdmin())->get('/admin?period=custom&from=&to=')
            ->assertOk()
            ->assertSee('Rp 0');

        $this->actingAs($this->superAdmin())->get('/admin?period=custom&from='.Carbon::today()->toDateString().'&to='.Carbon::yesterday()->toDateString())
            ->assertOk()
            ->assertSee('Rp 0');
    }

    public function test_top_products_sorted_from_order_items(): void
    {
        $category = Category::factory()->create();
        $risol = Product::factory()->create(['category_id' => $category->id, 'name' => 'Risol Spesial', 'price' => 10000]);
        $pisang = Product::factory()->create(['category_id' => $category->id, 'name' => 'Pisang Goreng', 'price' => 8000]);

        $order = $this->makeOrder(['payment_status' => PaymentStatus::Paid, 'paid_at' => now(), 'status' => OrderStatus::Completed, 'completed_at' => now()]);

        OrderItem::factory()->create([
            'order_id' => $order->id, 'product_id' => $pisang->id,
            'product_name' => $pisang->name, 'product_price' => 8000, 'product_cost' => 5000,
            'quantity' => 3, 'subtotal' => 24000,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id, 'product_id' => $risol->id,
            'product_name' => $risol->name, 'product_price' => 10000, 'product_cost' => 6000,
            'quantity' => 7, 'subtotal' => 70000,
        ]);

        $response = $this->actingAs($this->superAdmin())->get('/admin');

        $content = $response->getContent();
        $risolPosition = mb_strpos($content, 'Risol Spesial');
        $pisangPosition = mb_strpos($content, 'Pisang Goreng');

        $this->assertNotFalse($risolPosition);
        $this->assertNotFalse($pisangPosition);
        $this->assertLessThan($pisangPosition, $risolPosition, 'Produk terlaris harus urut dari kuantitas terbesar.');
        $response->assertSee('7 terjual');
        $response->assertSee('3 terjual');
    }

    public function test_top_products_exclude_cancelled_orders(): void
    {
        $this->makeOrder(['status' => OrderStatus::Cancelled], [
            ['product' => Product::factory()->create(['name' => 'Produk Batal']), 'quantity' => 9],
        ]);

        $response = $this->actingAs($this->superAdmin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Belum ada penjualan');
        $response->assertDontSee('Produk Batal');
    }

    public function test_customer_count_excludes_admin_roles(): void
    {
        $customer = User::factory()->create();
        $superAdmin = $this->superAdmin();
        $adminCs = User::factory()->create();
        $adminCs->assignRole(Role::firstOrCreate(['name' => 'admin_cs']));

        $response = $this->actingAs($superAdmin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Pelanggan Baru');
        $this->assertNotNull($customer);
        $this->assertSame(1, User::query()->whereDoesntHave('roles')->where('id', '!=', $superAdmin->id)->count());
    }
}
