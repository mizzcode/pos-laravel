<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('midtrans_order_id')->nullable();
            $table->string('metode_bayar')->nullable(); // credit_card, bank_transfer, etc
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('status_bayar')->default('pending'); // pending, settlement, cancel, expire
            $table->timestamp('waktu_bayar')->nullable();
            $table->text('payload_midtrans')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
