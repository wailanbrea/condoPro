<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\BillItem;
use App\Models\Condominium;
use App\Models\GasReading;
use App\Models\MonthlyBill;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MobileController extends Controller
{
    private AuditLogService $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    // ========================================
    // LOGIN
    // ========================================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas.'],
            ]);
        }

        // Only admins/super_admins can use the app
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo administradores pueden usar esta app.',
            ], 403);
        }

        $token = $user->createToken('condopro-mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'condominium_id' => $user->condominium_id,
            ],
        ]);
    }

    // ========================================
    // LIST CONDOMINIUMS
    // ========================================
    public function condominiums(Request $request)
    {
        $user = $request->user();

        $condominiums = $user->role === 'super_admin'
            ? Condominium::orderBy('name')->get()
            : Condominium::where('id', $user->condominium_id)->get();

        return response()->json([
            'success' => true,
            'data' => $condominiums->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address,
                'gas_price_per_gallon' => $c->gas_price_per_gallon ?? 147.20,
                'gas_conversion_factor' => $c->gas_conversion_factor ?? 1.20,
            ]),
        ]);
    }

    // ========================================
    // CURRENT PERIOD
    // ========================================
    public function currentPeriod(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'month' => (int) now()->month,
                'year' => (int) now()->year,
                'reading_start_date' => now()->startOfMonth()->format('Y-m-d'),
                'reading_end_date' => now()->endOfMonth()->format('Y-m-d'),
                'months' => $this->monthOptions(),
            ],
        ]);
    }

    // ========================================
    // APARTMENT READINGS LIST
    // ========================================
    public function apartmentReadings(Request $request)
    {
        $request->validate([
            'condominium_id' => 'required|integer|exists:condominiums,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);

        $user = $request->user();
        $condominiumId = $request->condominium_id;
        $month = $request->month;
        $year = $request->year;

        // Auth check
        if ($user->role === 'admin' && $user->condominium_id !== (int) $condominiumId) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $condominium = Condominium::findOrFail($condominiumId);

        $apartments = Apartment::where('condominium_id', $condominiumId)
            ->where('status', 'active')
            ->where('has_gas_meter', true)
            ->orderBy('number')
            ->get();

        // Get ALL readings for these apartments (not just current month)
        // because previous_reading comes from the last reading regardless of month
        $allReadings = GasReading::whereIn('apartment_id', $apartments->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('apartment_id');

        $data = $apartments->map(function ($apartment) use ($month, $year, $allReadings) {
            $readings = $allReadings->get($apartment->id, collect([]));

            // Get current month's reading
            $currentReading = $readings->first(fn($r) => 
                $r->billing_month == $month && $r->billing_year == $year
            );

            // Previous reading = the most recent confirmed reading before this month
            $previousReading = $readings->first(fn($r) => 
                ($r->billing_year < $year) || 
                ($r->billing_year == $year && $r->billing_month < $month)
            );

            return [
                'apartment_id' => $apartment->id,
                'apartment_number' => $apartment->number,
                'meter_number' => $currentReading?->meter_number ?? $previousReading?->meter_number ?? '',
                'previous_reading' => $currentReading?->reading_initial ?? null,
                'current_reading' => $currentReading?->reading_final ?? null,
                'previous_reading_value' => $previousReading?->reading_final ?? null,
                'consumption_m3' => $currentReading?->consumption_m3 ?? null,
                'total_amount' => $currentReading?->total_amount ?? null,
                'status' => !$currentReading ? 'pending' : ($currentReading->billed ? 'billed' : 'confirmed'),
                'reading_id' => $currentReading?->id ?? null,
                'photo_path' => $currentReading?->photo_path ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'settings' => [
                'conversion_factor' => $condominium->gas_conversion_factor ?? 1.20,
                'gallon_price' => $condominium->gas_price_per_gallon ?? 147.20,
                'extra_cost_per_gallon' => 0,
                'currency' => 'DOP',
            ],
            'period' => [
                'month' => (int) $month,
                'year' => (int) $year,
                'reading_start_date' => "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01",
                'reading_end_date' => \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d'),
            ],
        ]);
    }

    // ========================================
    // STORE NEW READING
    // ========================================
    public function storeReading(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|integer|exists:condominiums,id',
            'apartment_id' => 'required|integer|exists:apartments,id',
            'billing_month' => 'required|integer|min:1|max:12',
            'billing_year' => 'required|integer|min:2020',
            'reading_start_date' => 'required|date',
            'reading_end_date' => 'required|date|after_or_equal:reading_start_date',
            'previous_reading' => 'required|numeric|min:0',
            'current_reading' => 'required|numeric|min:0',
            'meter_number' => 'nullable|string|max:50',
        ]);

        $user = $request->user();

        // Auth check
        if ($user->role === 'admin' && $user->condominium_id !== (int) $validated['condominium_id']) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        // Validate reading not smaller than previous
        if ($validated['current_reading'] < $validated['previous_reading']) {
            return response()->json([
                'success' => false,
                'message' => 'La lectura actual no puede ser menor que la anterior.',
                'errors' => ['current_reading' => ['Debe ser mayor o igual a la lectura anterior.']],
            ], 422);
        }

        // Check duplicate
        $exists = GasReading::where('apartment_id', $validated['apartment_id'])
            ->where('billing_month', $validated['billing_month'])
            ->where('billing_year', $validated['billing_year'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una lectura para este apartamento en este período.',
            ], 422);
        }

        // Get condo settings
        $condominium = Condominium::find($validated['condominium_id']);
        $conversionFactor = $condominium->gas_conversion_factor ?? 1.20;
        $gallonPrice = $condominium->gas_price_per_gallon ?? 147.20;
        $extraCostPerGallon = 0;

        // Calculate gas values
        $consumptionM3 = round($validated['current_reading'] - $validated['previous_reading'], 3);
        $gallons = round($consumptionM3 * $conversionFactor, 2);
        $totalGallonPrice = round($gallonPrice + $extraCostPerGallon, 2);
        $totalAmount = round($gallons * $totalGallonPrice, 2);

        $reading = GasReading::create([
            'condominium_id' => $validated['condominium_id'],
            'apartment_id' => $validated['apartment_id'],
            'meter_number' => $validated['meter_number'] ?? null,
            'reading_date_start' => $validated['reading_start_date'],
            'reading_date_end' => $validated['reading_end_date'],
            'billing_month' => $validated['billing_month'],
            'billing_year' => $validated['billing_year'],
            'reading_initial' => $validated['previous_reading'],
            'reading_final' => $validated['current_reading'],
            'consumption_m3' => $consumptionM3,
            'conversion_factor' => $conversionFactor,
            'gallons' => $gallons,
            'gallon_price' => $gallonPrice,
            'extra_cost_per_gallon' => $extraCostPerGallon,
            'total_gallon_price' => $totalGallonPrice,
            'total_amount' => $totalAmount,
            'total_gas' => $totalAmount,
            'price_per_gallon' => $gallonPrice,
            'status' => 'active',
            'billed' => false,
            'created_by' => $user->id,
        ]);

        $this->auditLog->log('gas_reading_created_mobile', 'gas', $reading->id);

        // Auto-create or update bill for this apartment/period
        $bill = $this->createOrUpdateBillForGasReading($reading, $condominium);

        return response()->json([
            'success' => true,
            'message' => 'Lectura registrada y factura de gas creada correctamente.',
            'data' => [
                'gas_reading' => [
                    'id' => $reading->id,
                    'apartment_id' => $reading->apartment_id,
                    'previous_reading' => (float) $reading->reading_initial,
                    'current_reading' => (float) $reading->reading_final,
                    'consumption_m3' => (float) $reading->consumption_m3,
                    'gallons' => (float) $reading->gallons,
                    'total_amount' => (float) $reading->total_amount,
                    'status' => 'billed',
                ],
                'invoice' => $bill ? [
                    'id' => $bill->id,
                    'total' => (float) $bill->total,
                    'balance' => (float) max(0, $bill->total - $bill->payments_applied),
                    'status' => $bill->status,
                ] : null,
            ],
        ], 201);
    }

    // ========================================
    // BULK SYNC
    // ========================================
    public function bulkSync(Request $request)
    {
        $validated = $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.apartment_id' => 'required|integer|exists:apartments,id',
            'readings.*.condominium_id' => 'required|integer|exists:condominiums,id',
            'readings.*.billing_month' => 'required|integer|min:1|max:12',
            'readings.*.billing_year' => 'required|integer|min:2020',
            'readings.*.reading_start_date' => 'required|date',
            'readings.*.reading_end_date' => 'required|date',
            'readings.*.previous_reading' => 'required|numeric|min:0',
            'readings.*.current_reading' => 'required|numeric|min:0',
            'readings.*.meter_number' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $results = [];
        $errors = [];

        foreach ($validated['readings'] as $index => $readingData) {
            try {
                // Auth check
                if ($user->role === 'admin' && $user->condominium_id !== (int) $readingData['condominium_id']) {
                    $errors[] = ['index' => $index, 'message' => 'No autorizado para este condominio.'];
                    continue;
                }

                // Check duplicate
                $exists = GasReading::where('apartment_id', $readingData['apartment_id'])
                    ->where('billing_month', $readingData['billing_month'])
                    ->where('billing_year', $readingData['billing_year'])
                    ->exists();

                if ($exists) {
                    $errors[] = ['index' => $index, 'message' => "Lectura duplicada para apto {$readingData['apartment_id']}."];
                    continue;
                }

                if ($readingData['current_reading'] < $readingData['previous_reading']) {
                    $errors[] = ['index' => $index, 'message' => 'Lectura actual menor que la anterior.'];
                    continue;
                }

                $condominium = Condominium::find($readingData['condominium_id']);
                $conversionFactor = $condominium->gas_conversion_factor ?? 1.20;
                $gallonPrice = $condominium->gas_price_per_gallon ?? 147.20;
                $extraCostPerGallon = 0;

                $consumptionM3 = round($readingData['current_reading'] - $readingData['previous_reading'], 3);
                $gallons = round($consumptionM3 * $conversionFactor, 2);
                $totalGallonPrice = round($gallonPrice + $extraCostPerGallon, 2);
                $totalAmount = round($gallons * $totalGallonPrice, 2);

                $reading = GasReading::create([
                    'condominium_id' => $readingData['condominium_id'],
                    'apartment_id' => $readingData['apartment_id'],
                    'meter_number' => $readingData['meter_number'] ?? null,
                    'reading_date_start' => $readingData['reading_start_date'],
                    'reading_date_end' => $readingData['reading_end_date'],
                    'billing_month' => $readingData['billing_month'],
                    'billing_year' => $readingData['billing_year'],
                    'reading_initial' => $readingData['previous_reading'],
                    'reading_final' => $readingData['current_reading'],
                    'consumption_m3' => $consumptionM3,
                    'conversion_factor' => $conversionFactor,
                    'gallons' => $gallons,
                    'gallon_price' => $gallonPrice,
                    'extra_cost_per_gallon' => $extraCostPerGallon,
                    'total_gallon_price' => $totalGallonPrice,
                    'total_amount' => $totalAmount,
                    'total_gas' => $totalAmount,
                    'price_per_gallon' => $gallonPrice,
                    'status' => 'active',
                    'billed' => false,
                    'created_by' => $user->id,
                ]);

                $this->createOrUpdateBillForGasReading($reading, $condominium);

                $results[] = [
                    'apartment_id' => $reading->apartment_id,
                    'id' => $reading->id,
                    'consumption_m3' => (float) $reading->consumption_m3,
                    'gallons' => (float) $reading->gallons,
                    'total_amount' => (float) $reading->total_amount,
                    'status' => 'billed',
                    'invoice_created' => true,
                ];
            } catch (\Exception $e) {
                $errors[] = ['index' => $index, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => count($errors) === 0,
            'synced' => count($results),
            'errors_count' => count($errors),
            'data' => $results,
            'errors' => $errors,
        ]);
    }

    // ========================================
    // UPLOAD PHOTO
    // ========================================
    public function uploadPhoto(Request $request, GasReading $reading)
    {
        $user = $request->user();

        if ($user->role === 'admin' && $user->condominium_id !== $reading->condominium_id) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Delete old photo if exists
        if ($reading->photo_path) {
            Storage::disk('public')->delete($reading->photo_path);
        }

        $path = $request->file('photo')->store('gas-readings', 'public');
        $reading->update(['photo_path' => $path]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reading->id,
                'photo_url' => Storage::url($path),
            ],
        ]);
    }

    // ========================================
    // HELPERS
    // ========================================
    private function monthOptions(): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = [
                'value' => $m,
                'label' => \Carbon\Carbon::create()->month($m)->locale('es')->monthName,
            ];
        }
        return $months;
    }

    private function createOrUpdateBillForGasReading(GasReading $reading, Condominium $condominium): ?MonthlyBill
    {
        $apartment = Apartment::find($reading->apartment_id);
        if (!$apartment) {
            return null;
        }

        return DB::transaction(function () use ($reading, $condominium, $apartment) {
            $bill = MonthlyBill::where('apartment_id', $apartment->id)
                ->where('billing_month', $reading->billing_month)
                ->where('billing_year', $reading->billing_year)
                ->first();

            $gasItem = sprintf(
                'Gas: %s m³ × %s = %s gal × RD$%s = RD$%s',
                number_format($reading->consumption_m3, 3),
                number_format($reading->conversion_factor, 4),
                number_format($reading->gallons, 2),
                number_format($reading->total_gallon_price, 2),
                number_format($reading->total_amount, 2)
            );

            if ($bill) {
                $existingGasItem = $bill->billItems()->where('concept_type', 'gas')->first();

                if ($existingGasItem) {
                    if ($bill->status === 'paid') {
                        return $bill;
                    }

                    $oldAmount = $existingGasItem->amount;
                    $existingGasItem->update([
                        'description' => $gasItem,
                        'amount' => $reading->total_amount,
                        'reference_id' => $reading->id,
                    ]);

                    $bill->subtotal = ($bill->subtotal - $oldAmount) + $reading->total_amount;
                    $bill->total = $bill->subtotal - abs($bill->previous_balance);
                    $bill->save();
                } else {
                    BillItem::create([
                        'bill_id' => $bill->id,
                        'concept_type' => 'gas',
                        'description' => $gasItem,
                        'amount' => $reading->total_amount,
                        'reference_id' => $reading->id,
                    ]);

                    $bill->subtotal += $reading->total_amount;
                    $bill->total = $bill->subtotal - abs($bill->previous_balance);
                    $bill->save();
                }

                $reading->update(['billed' => true]);
                $this->auditLog->log('gas_bill_item_added', 'billing', $bill->id);

                return $bill;
            }

            if (!$apartment->has_gas_meter || $reading->total_amount <= 0) {
                $reading->update(['billed' => true]);
                return null;
            }

            $dueDate = now()->setDate($reading->billing_year, $reading->billing_month, min(28, now()->setDate($reading->billing_year, $reading->billing_month, 1)->daysInMonth))->format('Y-m-d');

            $previousBalance = MonthlyBill::where('apartment_id', $apartment->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->get()
                ->sum(fn($b) => max(0, $b->total - $b->payments_applied));

            $subtotal = 0;

            $bill = MonthlyBill::create([
                'condominium_id' => $condominium->id,
                'apartment_id' => $apartment->id,
                'billing_month' => $reading->billing_month,
                'billing_year' => $reading->billing_year,
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
                    'description' => 'Mantenimiento',
                    'amount' => $maintenanceAmount,
                ]);
                $subtotal += $maintenanceAmount;
            }

            BillItem::create([
                'bill_id' => $bill->id,
                'concept_type' => 'gas',
                'description' => $gasItem,
                'amount' => $reading->total_amount,
                'reference_id' => $reading->id,
            ]);
            $subtotal += $reading->total_amount;

            $installments = \App\Models\ExtraChargeInstallment::where('apartment_id', $apartment->id)
                ->where('billing_month', $reading->billing_month)
                ->where('billing_year', $reading->billing_year)
                ->where('status', 'pending')
                ->get();

            foreach ($installments as $installment) {
                $billItem = BillItem::create([
                    'bill_id' => $bill->id,
                    'concept_type' => 'extra_charge',
                    'description' => $installment->extraCharge->title ?? 'Cuota Extra',
                    'amount' => $installment->amount,
                    'reference_id' => $installment->extra_charge_id,
                ]);
                $subtotal += $installment->amount;
                $installment->update(['status' => 'billed', 'bill_item_id' => $billItem->id]);
            }

            $bill->update([
                'subtotal' => $subtotal,
                'total' => $subtotal - abs($bill->previous_balance),
            ]);

            $reading->update(['billed' => true]);

            $this->auditLog->log('gas_bill_created_mobile', 'billing', $bill->id);

            return $bill;
        });
    }
}