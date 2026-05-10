<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Models\BillItem;
use App\Models\Condominium;
use App\Models\ExtraChargeInstallment;
use App\Models\GasReading;
use App\Models\MonthlyBill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyBills extends Command
{
    protected $signature = 'bills:generate 
                            {--month= : Month number (1-12), defaults to current month} 
                            {--year= : Year, defaults to current year} 
                            {--condominium= : Specific condominium ID to process} 
                            {--force : Force generation even if bills already exist}';

    protected $description = 'Generate monthly bills for all active apartments';

    public function handle(): int
    {
        $month = (int) ($this->option('month') ?: now()->month);
        $year = (int) ($this->option('year') ?: now()->year);
        $condominiumId = $this->option('condominium');
        $force = $this->option('force');

        $this->info("Generating bills for {$month}/{$year}...");

        $condominiums = $condominiumId
            ? Condominium::where('id', $condominiumId)->where('status', 'active')->get()
            : Condominium::where('status', 'active')->get();

        if ($condominiums->isEmpty()) {
            $this->warn('No active condominiums found.');
            return self::SUCCESS;
        }

        $billsCreated = 0;
        $billsSkipped = 0;

        foreach ($condominiums as $condominium) {
            $this->info("Processing condominium: {$condominium->name}");

            $apartments = Apartment::where('condominium_id', $condominium->id)
                ->where('status', 'active')
                ->get();

            foreach ($apartments as $apartment) {
                $exists = MonthlyBill::where('apartment_id', $apartment->id)
                    ->where('billing_month', $month)
                    ->where('billing_year', $year)
                    ->exists();

                if ($exists && !$force) {
                    $this->warn("  Skipped apartment {$apartment->number} - bill already exists.");
                    $billsSkipped++;
                    continue;
                }

                if ($exists && $force) {
                    $existing = MonthlyBill::where('apartment_id', $apartment->id)
                        ->where('billing_month', $month)
                        ->where('billing_year', $year)
                        ->first();
                    if ($existing->payments_applied > 0 || $existing->status === 'paid') {
                        $this->warn("  Skipped apartment {$apartment->number} - bill has payments applied ({$existing->payments_applied}), status: {$existing->status}.");
                        $billsSkipped++;
                        continue;
                    }
                    $existing->billItems()->delete();
                    $existing->delete();
                }

                $dueDate = now()->setDate($year, $month, min(30, now()->setDate($year, $month, 1)->daysInMonth))->format('Y-m-d');

                try {
                    DB::transaction(function () use ($apartment, $condominium, $month, $year, $dueDate) {
                        $subtotal = 0;

                        // Calculate previous balance from outstanding bills only
                        $previousBalance = MonthlyBill::where('apartment_id', $apartment->id)
                            ->whereIn('status', ['pending', 'partial', 'overdue'])
                            ->get()
                            ->sum(function ($b) {
                                return max(0, $b->total - $b->payments_applied);
                            });

                        $bill = MonthlyBill::create([
                            'condominium_id' => $condominium->id,
                            'apartment_id' => $apartment->id,
                            'billing_month' => $month,
                            'billing_year' => $year,
                            'subtotal' => 0,
                            'previous_balance' => -$previousBalance,
                            'payments_applied' => 0,
                            'total' => 0,
                            'due_date' => $dueDate,
                            'status' => 'pending',
                        ]);

                        $maintenanceAmount = $apartment->maintenance_fee;
                        if ($maintenanceAmount > 0) {
                            BillItem::create([
                                'bill_id' => $bill->id,
                                'concept_type' => 'maintenance',
                                'description' => __('messages.billing.concept_maintenance'),
                                'amount' => $maintenanceAmount,
                            ]);
                            $subtotal += $maintenanceAmount;
                        }

                        $gasReading = GasReading::where('apartment_id', $apartment->id)
                            ->where('billed', false)
                            ->orderBy('reading_date_end', 'desc')
                            ->first();

                        if ($gasReading && $apartment->has_gas_meter && $gasReading->total_amount > 0) {
                            $gasDescription = sprintf(
                                'Gas: %s m³ × %s = %s gal × RD$%s = RD$%s',
                                number_format($gasReading->consumption_m3, 3),
                                number_format($gasReading->conversion_factor, 4),
                                number_format($gasReading->gallons, 2),
                                number_format($gasReading->total_gallon_price, 2),
                                number_format($gasReading->total_amount, 2)
                            );
                            BillItem::create([
                                'bill_id' => $bill->id,
                                'concept_type' => 'gas',
                                'description' => $gasDescription,
                                'amount' => $gasReading->total_amount,
                                'reference_id' => $gasReading->id,
                            ]);
                            $subtotal += $gasReading->total_amount;
                            $gasReading->update(['billed' => true]);
                        }

                        $installments = ExtraChargeInstallment::where('apartment_id', $apartment->id)
                            ->where('billing_month', $month)
                            ->where('billing_year', $year)
                            ->where('status', 'pending')
                            ->get();

                        foreach ($installments as $installment) {
                            $billItem = BillItem::create([
                                'bill_id' => $bill->id,
                                'concept_type' => 'extra_charge',
                                'description' => $installment->extraCharge->title ?? __('messages.billing.concept_extra_charge'),
                                'amount' => $installment->amount,
                                'reference_id' => $installment->extra_charge_id,
                            ]);
                            $subtotal += $installment->amount;
                            $installment->update(['status' => 'billed', 'bill_item_id' => $billItem->id]);
                        }

                        $bill->update([
                            'subtotal' => $subtotal,
                            'total' => $subtotal - $bill->previous_balance,
                        ]);
                    });

                    $this->info("  Created bill for apartment {$apartment->number}.");
                    $billsCreated++;
                } catch (\Exception $e) {
                    $this->error("  Error creating bill for apartment {$apartment->number}: {$e->getMessage()}");
                    Log::error("Bill generation error: {$e->getMessage()}", [
                        'apartment_id' => $apartment->id,
                        'month' => $month,
                        'year' => $year,
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info("Bills created: {$billsCreated}");
        $this->info("Bills skipped: {$billsSkipped}");

        return self::SUCCESS;
    }
}