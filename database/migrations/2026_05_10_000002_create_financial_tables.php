<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_financial_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('initial_balance', 14, 2)->default(0);
            $table->decimal('total_income', 14, 2)->default(0);
            $table->decimal('total_expenses', 14, 2)->default(0);
            $table->decimal('special_payments', 14, 2)->default(0);
            $table->decimal('final_balance', 14, 2)->default(0);
            $table->decimal('total_maintenance', 14, 2)->default(0);
            $table->decimal('total_gas', 14, 2)->default(0);
            $table->decimal('total_extra_charges', 14, 2)->default(0);
            $table->decimal('total_pending', 14, 2)->default(0);
            $table->string('status', 20)->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['condominium_id', 'month', 'year']);
        });

        Schema::create('financial_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->enum('movement_type', ['income', 'expense', 'adjustment']);
            $table->string('category', 100);
            $table->decimal('amount', 14, 2);
            $table->string('description')->nullable();
            $table->foreignId('reference_id')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->date('movement_date');
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['condominium_id', 'movement_type']);
            $table->index(['condominium_id', 'month', 'year']);
            $table->index('movement_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_movements');
        Schema::dropIfExists('monthly_financial_reports');
    }
};