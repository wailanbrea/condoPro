<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->integer('billing_month');
            $table->integer('billing_year');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('previous_balance', 12, 2)->default(0);
            $table->decimal('payments_applied', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('due_date');
            $table->timestamps();

            $table->unique(['apartment_id', 'billing_month', 'billing_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_bills');
    }
};