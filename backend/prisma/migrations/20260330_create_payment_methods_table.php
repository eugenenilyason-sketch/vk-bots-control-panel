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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->string('id')->primary(); // yoomoney, card, sbp, crypto
            $table->string('name'); // YooMoney
            $table->string('title'); // YooMoney P2P
            $table->string('type'); // p2p, card, qr, crypto
            $table->text('description')->nullable();
            $table->string('icon')->default('💳');
            $table->boolean('enabled')->default(true);
            $table->decimal('min_amount', 10, 2)->default(100);
            $table->decimal('max_amount', 10, 2)->default(100000);
            $table->decimal('commission', 5, 2)->default(0);
            
            // API ключи (будут хэшироваться)
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('merchant_id')->nullable();
            
            // Дополнительные настройки
            $table->json('settings')->nullable();
            
            $table->timestamps();
        });
        
        // Добавляем индекс для enabled
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
