<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gas_readings', function (Blueprint $table) {
            $table->foreignId('condominium_id')->nullable()->after('id')->constrained('condominiums')->nullOnDelete();
            $table->string('meter_number')->nullable()->after('apartment_id');
            $table->decimal('gallon_price', 10, 2)->default(0)->after('price_per_gallon');
            $table->decimal('extra_cost_per_gallon', 10, 2)->default(0)->after('gallon_price');
            $table->decimal('total_gallon_price', 10, 2)->default(0)->after('extra_cost_per_gallon');
            $table->decimal('total_amount', 10, 2)->default(0)->after('total_gas');
            $table->integer('billing_month')->nullable()->after('total_amount');
            $table->integer('billing_year')->nullable()->after('billing_month');
            $table->boolean('billed')->default(false)->after('billing_year');
            $table->foreignId('created_by')->nullable()->after('billed')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gas_readings', function (Blueprint $table) {
            $table->dropForeign(['condominium_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'condominium_id', 'meter_number', 'gallon_price',
                'extra_cost_per_gallon', 'total_gallon_price', 'total_amount',
                'billing_month', 'billing_year', 'billed', 'created_by'
            ]);
        });
    }
};