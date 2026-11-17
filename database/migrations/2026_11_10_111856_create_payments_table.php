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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20)->comment('qris|bank_transfer|ewallet|cod');
            $table->string('status', 30)->default('pending_verification')
                ->comment('pending_verification|verified|rejected|refunded');
            $table->decimal('amount', 12, 2);
            $table->string('proof_path')->nullable()->comment('Bukti pembayaran (upload)');
            $table->string('payer_name')->nullable()->comment('Nama pengirim/rekening pengirim');
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
