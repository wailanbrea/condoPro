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
        Schema::create('gas_tank_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->string('tank_name')->default('Tanque Principal');
            $table->decimal('capacity_gallons', 10, 2)->default(100);
            $table->decimal('alert_min_gallons', 10, 2)->default(20);
            $table->decimal('alert_min_percentage', 5, 2)->default(20);
            $table->string('average_consumption_method')->default('last_3_months');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique('condominium_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_tank_settings');
    }
};
