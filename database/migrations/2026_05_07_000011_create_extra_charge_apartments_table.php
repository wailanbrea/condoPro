<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_charge_apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extra_charge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->decimal('assigned_amount', 12, 2)->default(0);
            $table->decimal('monthly_amount', 12, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_charge_apartments');
    }
};