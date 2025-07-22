<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipe_order')->default('online'); // online, offline
            $table->date('tanggal_order');
            $table->decimal('total_order', 15, 2);
            $table->string('status_order')->default('pending'); // pending, processing, completed, cancelled
            $table->text('alamat_kirim')->nullable();
            $table->text('catatan')->nullable();
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
