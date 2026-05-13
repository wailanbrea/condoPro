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
        Schema::table('gas_tank_settings', function (Blueprint $table) {
            $table->decimal('last_reading', 10, 2)->nullable()->after('status');
            $table->dateTime('last_reading_date')->nullable()->after('last_reading');
        });
    }

    public function down(): void
    {
        Schema::table('gas_tank_settings', function (Blueprint $table) {
            $table->dropColumn(['last_reading', 'last_reading_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gas_tank_settings', function (Blueprint $table) {
            //
        });
    }
};
