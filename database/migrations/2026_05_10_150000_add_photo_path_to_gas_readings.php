<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gas_readings', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('billed');

            // Unique constraint: one reading per apartment per billing period
            $table->unique(['condominium_id', 'apartment_id', 'billing_month', 'billing_year'], 
                'gas_readings_apartment_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('gas_readings', function (Blueprint $table) {
            $table->dropUnique('gas_readings_apartment_period_unique');
            $table->dropColumn('photo_path');
        });
    }
};