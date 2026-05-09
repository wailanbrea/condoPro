<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->string('number');
            $table->string('owner_name')->nullable();
            $table->string('status')->default('active');
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('maintenance_fee', 12, 2)->default(0);
            $table->boolean('has_gas_meter')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};