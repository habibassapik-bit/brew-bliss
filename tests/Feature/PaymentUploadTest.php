<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_upload_proof_for_own_order(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store', $order), [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
            'payer_name' => 'Budi Santoso',
            'reference_number' => 'TRX-123',
        ]);

        $response->assertRedirect(route('orders.show', $order));
        $order->refresh();

        $payment = $order->payment;
        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::PendingVerification, $payment->status);
        $this->assertSame(PaymentStatus::PendingVerification, $order->payment_status);
        $this->assertSame('Budi Santoso', $payment->payer_name);
        $this->assertSame('TRX-123', $payment->reference_number);
        $this->assertNotNull($payment->proof_path);
        Storage::disk('local')->assertExists($payment->proof_path);
        $this->assertStringNotContainsString('bukti', $payment->proof_path, 'Nama file dari client tidak boleh dipakai.');
    }

    public function test_other_user_cannot_upload_proof_to_another_order(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => User::factory()->create()->id]);
        $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store', $order), [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertNotFound();
        $order->refresh();
        $this->assertSame(PaymentStatus::Unpaid, $order->payment->status);
        $this->assertNull($order->payment->proof_path);
    }

    public function test_upload_rejects_invalid_file_type(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store', $order), [
            'proof' => UploadedFile::fake()->create('bukti.pdf', 100),
        ]);

        $response->assertSessionHasErrors('proof');
        $order->refresh();
        $this->assertSame(PaymentStatus::Unpaid, $order->payment->status);
    }

    public function test_reupload_replaces_proof_without_duplicating_payment(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->total,
        ]);

        $this->actingAs($user)->post(route('payments.store', $order), [
            'proof' => UploadedFile::fake()->image('pertama.jpg'),
        ]);

        $firstPath = $order->fresh()->payment->proof_path;
        $this->assertNotNull($firstPath);

        $this->actingAs($user)->post(route('payments.store', $order), [
            'proof' => UploadedFile::fake()->image('kedua.png'),
        ]);

        $order->refresh();
        $payment = $order->payment;

        $this->assertSame(1, $order->payment()->count());
        $this->assertNotSame($firstPath, $payment->proof_path);
        Storage::disk('local')->assertExists($payment->proof_path);
        Storage::disk('local')->assertMissing($firstPath);
        $this->assertSame(PaymentStatus::PendingVerification, $payment->status);
    }

    public function test_cod_order_does_not_require_proof(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->payment()->create([
            'method' => PaymentMethod::Cod,
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->total,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store', $order), [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertSessionHasErrors('proof');
        $order->refresh();
        $this->assertSame(PaymentStatus::Unpaid, $order->payment->status);
        $this->assertNull($order->payment->proof_path);
    }

    public function test_proof_file_only_accessible_by_owner_and_super_admin(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::create(['name' => 'super_admin']));

        $order = Order::factory()->create(['user_id' => $owner->id]);
        $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::PendingVerification,
            'amount' => $order->total,
            'proof_path' => 'payment-proofs/test-proof.jpg',
        ]);
        Storage::disk('local')->put($order->payment->proof_path, 'fake-image');

        $this->actingAs($owner)->get(route('payments.proof', $order))->assertOk();
        $this->actingAs($other)->get(route('payments.proof', $order))->assertNotFound();
        $this->actingAs($superAdmin)->get(route('payments.proof', $order))->assertOk();
    }

    public function test_guest_cannot_upload_proof(): void
    {
        Storage::fake('local');
        $order = Order::factory()->create();
        $order->payment()->create([
            'method' => PaymentMethod::BankTransfer,
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->total,
        ]);

        $response = $this->post(route('payments.store', $order), [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect('/masuk');
        $order->refresh();
        $this->assertSame(PaymentStatus::Unpaid, $order->payment->status);
        $this->assertNull($order->payment->proof_path);
        $this->assertDatabaseCount('payments', 1);
    }
}
