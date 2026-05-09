<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gas_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->date('reading_date_start');
            $table->date('reading_date_end');
            $table->decimal('reading_initial', 12, 2)->default(0);
            $table->decimal('reading_final', 12, 2)->default(0);
            $table->decimal('consumption_m3', 12, 2)->default(0);
            $table->decimal('conversion_factor', 10, 4)->default(0);
            $table->decimal('gallons', 12, 2)->default(0);
            $table->decimal('price_per_gallon', 12, 2)->default(0);
            $table->decimal('total_gas', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gas_readings');
    }
};