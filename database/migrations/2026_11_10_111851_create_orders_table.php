<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('pending_confirmation')
                ->comment('pending_confirmation|awaiting_payment|processing|ready_pickup|shipping|completed|cancelled');
            $table->string('payment_status', 30)->default('unpaid')
                ->comment('unpaid|pending_verification|paid|rejected|refunded');
            $table->string('fulfillment_type', 10)->comment('delivery|pickup');
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->text('delivery_address')->nullable()->comment('Snapshot alamat saat checkout');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('note')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
