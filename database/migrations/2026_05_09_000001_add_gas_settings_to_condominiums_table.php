<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->decimal('gas_price_per_gallon', 10, 2)->nullable()->after('language_default');
            $table->decimal('gas_conversion_factor', 10, 4)->nullable()->after('gas_price_per_gallon');
        });
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn(['gas_price_per_gallon', 'gas_conversion_factor']);
        });
    }
};