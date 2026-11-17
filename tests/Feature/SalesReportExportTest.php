<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Exports\SalesReportExport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesReportExportTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $superAdmin;
    }

    private function createPaidOrder(string $orderNumber, ?Carbon $createdAt = null): Order
    {
        $order = Order::factory()->paid()->create([
            'status' => OrderStatus::Completed,
            'order_number' => $orderNumber,
            'subtotal' => 20000,
            'discount_amount' => 0,
            'delivery_fee' => 5000,
            'total' => 25000,
            'created_at' => $createdAt ?? Carbon::today(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => Product::factory()->create()->id,
            'product_name' => 'Produk Uji',
            'product_price' => 20000,
            'product_cost' => 8000,
            'quantity' => 1,
            'subtotal' => 20000,
        ]);

        return $order;
    }

    public function test_export_requires_super_admin_role(): void
    {
        $customer = User::factory()->create();

        $this->get(route('admin.reports.export'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.reports.export'))->assertForbidden();
    }

    public function test_export_follows_active_period_filter(): void
    {
        Excel::fake();

        $superAdmin = $this->createSuperAdmin();

        $this->createPaidOrder('BB-INRANGE-0001');
        $this->createPaidOrder('BB-OUTRANGE-0001', Carbon::yesterday());

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.export', ['period' => 'today']));

        $response->assertOk();

        Excel::assertDownloaded(
            'laporan-penjualan-'.Carbon::today()->toDateString().'-sd-'.Carbon::today()->toDateString().'.xlsx',
            function (object $export): bool {
                /** @var SalesReportExport $export */
                $orderNumbers = array_column($export->detail, 'order_number');

                return in_array('BB-INRANGE-0001', $orderNumbers, true)
                    && ! in_array('BB-OUTRANGE-0001', $orderNumbers, true)
                    && count($orderNumbers) === 1;
            }
        );
    }

    public function test_export_detail_uses_snapshot_values(): void
    {
        Excel::fake();

        $superAdmin = $this->createSuperAdmin();

        $order = $this->createPaidOrder('BB-SNAPSHOT-0001');

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.export'));

        $response->assertOk();

        Excel::assertDownloaded(
            'laporan-penjualan-'.Carbon::today()->toDateString().'-sd-'.Carbon::today()->toDateString().'.xlsx',
            function (object $export) use ($order): bool {
                /** @var SalesReportExport $export */
                if ($export->detail === []) {
                    return false;
                }

                $row = $export->detail[0];

                return $row['order_number'] === $order->order_number
                    && $row['total'] === 'Rp 25.000'
                    && $row['delivery_fee'] === 'Rp 5.000'
                    && $row['payment_status'] === 'Dibayar';
            }
        );
    }

    public function test_export_summary_contains_profit_rows(): void
    {
        Excel::fake();

        $superAdmin = $this->createSuperAdmin();

        $this->createPaidOrder('BB-SUMMARY-0001');

        $response = $this->actingAs($superAdmin)->get(route('admin.reports.export'));

        $response->assertOk();

        Excel::assertDownloaded(
            'laporan-penjualan-'.Carbon::today()->toDateString().'-sd-'.Carbon::today()->toDateString().'.xlsx',
            function (object $export): bool {
                /** @var SalesReportExport $export */
                $labels = array_column($export->summary, 0);

                return in_array('Omzet', $labels, true)
                    && in_array('Modal (HPP)', $labels, true)
                    && in_array('Laba kotor', $labels, true)
                    && in_array('Biaya operasional', $labels, true)
                    && in_array('Laba bersih', $labels, true);
            }
        );
    }
}
