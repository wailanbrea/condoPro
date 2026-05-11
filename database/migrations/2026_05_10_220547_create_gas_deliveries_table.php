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
        Schema::create('gas_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->decimal('tank_reading_before', 12, 3)->nullable();
            $table->decimal('tank_reading_after', 12, 3)->nullable();
            $table->decimal('truck_meter_reading', 12, 3)->nullable();
            $table->decimal('gallons_delivered', 10, 2)->nullable();
            $table->decimal('invoice_amount', 12, 2)->nullable();
            $table->string('tank_photo_before_path')->nullable();
            $table->string('tank_photo_after_path')->nullable();
            $table->string('truck_photo_path')->nullable();
            $table->string('invoice_photo_path')->nullable();
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['pending', 'receiving', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_deliveries');
    }
};
