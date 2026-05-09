<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_charge_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extra_charge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->integer('billing_month');
            $table->integer('billing_year');
            $table->decimal('amount', 12, 2)->default(0);
            $table->foreignId('bill_item_id')->nullable()->constrained('bill_items')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_charge_installments');
    }
};