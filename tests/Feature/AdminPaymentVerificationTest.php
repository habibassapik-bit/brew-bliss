<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_payment_verification(): void
    {
        $this->get(route('admin.payments.index'))->assertRedirect('/masuk');
    }

    public function test_customer_cannot_access_payment_verification(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->create();
        $payment = $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
        ]);

        $this->actingAs($customer)->get(route('admin.payments.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.payments.approve', $payment))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.payments.reject', $payment))->assertForbidden();
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending_verification']);
    }

    public function test_super_admin_can_see_pending_payments(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create(['order_number' => 'BB-VERIFY-01']);
        $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.payments.index'));

        $response->assertOk();
        $response->assertSee('BB-VERIFY-01');
        $response->assertSee('Menunggu Verifikasi');
    }

    public function test_super_admin_can_approve_payment_and_order_moves_to_processing(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create();
        $payment = $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.payments.approve', $payment));

        $response->assertRedirect();
        $payment->refresh();
        $order->refresh();

        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame($superAdmin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Processing, $order->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'processing',
            'changed_by' => $superAdmin->id,
        ]);
    }

    public function test_super_admin_can_reject_payment_with_reason(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create();
        $payment = $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.payments.reject', $payment), [
            'rejection_reason' => 'Jumlah transfer tidak sesuai',
        ]);

        $response->assertRedirect();
        $payment->refresh();
        $order->refresh();

        $this->assertSame(PaymentStatus::Rejected, $payment->status);
        $this->assertSame('Jumlah transfer tidak sesuai', $payment->rejection_reason);
        $this->assertSame($superAdmin->id, $payment->verified_by);
        $this->assertSame(PaymentStatus::Rejected, $order->payment_status);
        $this->assertNotSame(PaymentStatus::Paid, $order->payment_status);
    }

    public function test_reject_requires_reason(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create();
        $payment = $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.payments.reject', $payment), []);

        $response->assertSessionHasErrors('rejection_reason');
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending_verification']);
    }

    public function test_payment_cannot_be_approved_twice(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create();
        $payment = $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
        ]);

        $this->actingAs($superAdmin)->post(route('admin.payments.approve', $payment))->assertRedirect();

        $response = $this->actingAs($superAdmin)->post(route('admin.payments.approve', $payment));

        $response->assertSessionHasErrors('payment');
        $this->assertDatabaseCount('order_status_histories', 1);
    }

    public function test_rejected_payment_cannot_be_approved(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create();
        $payment = $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Rejected,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.payments.approve', $payment));

        $response->assertSessionHasErrors('payment');
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'rejected']);
    }
}
