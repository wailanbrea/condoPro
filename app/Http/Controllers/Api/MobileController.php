<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\BillItem;
use App\Models\Condominium;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GasDelivery;
use App\Models\GasReading;
use App\Models\GasTankSetting;
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

        return response()->json([
            'success' => true,
            'message' => 'Lectura registrada correctamente.',
            'data' => [
                'id' => $reading->id,
                'apartment_id' => $reading->apartment_id,
                'previous_reading' => (float) $reading->reading_initial,
                'current_reading' => (float) $reading->reading_final,
                'consumption_m3' => (float) $reading->consumption_m3,
                'gallons' => (float) $reading->gallons,
                'total_amount' => (float) $reading->total_amount,
                'status' => 'confirmed',
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

                $results[] = [
                    'apartment_id' => $reading->apartment_id,
                    'id' => $reading->id,
                    'consumption_m3' => (float) $reading->consumption_m3,
                    'gallons' => (float) $reading->gallons,
                    'total_amount' => (float) $reading->total_amount,
                    'status' => 'confirmed',
                    'invoice_created' => false,
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
    // GAS INVENTORY
    // ========================================

    public function gasInventory(Request $request)
    {
        $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
        ]);

        $condominium = Condominium::find($request->condominium_id);
        $setting = GasTankSetting::getForCondominium($condominium->id);

        $totalDelivered = GasDelivery::where('condominium_id', $condominium->id)
            ->where('status', 'completed')
            ->sum('gallons_delivered');

        $lastDelivery = GasDelivery::where('condominium_id', $condominium->id)
            ->where('status', 'completed')
            ->orderBy('delivery_date', 'desc')
            ->first();

        $consumptionSinceLastDelivery = $lastDelivery
            ? (float) GasReading::where('condominium_id', $condominium->id)->where('created_at', '>=', $lastDelivery->created_at)->sum('gallons')
            : 0;

        $estimatedInventory = max(0, (float) $totalDelivered - $consumptionSinceLastDelivery);
        $estimatedInventory = min($estimatedInventory, (float) $setting->capacity_gallons);
        $availablePercentage = $setting->capacity_gallons > 0
            ? round(($estimatedInventory / (float) $setting->capacity_gallons) * 100, 2)
            : 0;

        $status = 'normal';
        if ($setting->status !== 'active') {
            $status = 'inactive';
        } elseif ($estimatedInventory <= (float) $setting->alert_min_gallons || $availablePercentage <= (float) $setting->alert_min_percentage) {
            $status = 'low';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tank_name' => $setting->tank_name,
                'capacity_gallons' => (float) $setting->capacity_gallons,
                'estimated_current_inventory' => round($estimatedInventory, 2),
                'available_percentage' => $availablePercentage,
                'alert_min_gallons' => (float) $setting->alert_min_gallons,
                'alert_min_percentage' => (float) $setting->alert_min_percentage,
                'average_consumption_method' => $setting->average_consumption_method,
                'status' => $status,
            ],
        ]);
    }

    // ========================================
    // GAS DELIVERIES
    // ========================================

    public function storeGasDelivery(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'tank_reading_before' => 'required|numeric|gte:0',
            'tank_photo_before' => 'nullable|image|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        if ($user->role === 'admin' && (int) $validated['condominium_id'] !== $user->condominium_id) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $delivery = GasDelivery::create([
            'condominium_id' => $validated['condominium_id'],
            'tank_reading_before' => $validated['tank_reading_before'],
            'status' => 'receiving',
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        if ($request->hasFile('tank_photo_before')) {
            $path = $request->file('tank_photo_before')->store('gas-deliveries', 'public');
            $delivery->update(['tank_photo_before_path' => $path]);
        }

        $this->auditLog->log('gas_delivery_started', 'gas', $delivery->id);

        return response()->json([
            'success' => true,
            'message' => 'Recepción de gas iniciada correctamente.',
            'data' => [
                'id' => $delivery->id,
                'status' => $delivery->status,
                'tank_reading_before' => (float) $delivery->tank_reading_before,
            ],
        ], 201);
    }

    public function updateGasDeliveryReceiving(Request $request, GasDelivery $gasDelivery)
    {
        $validated = $request->validate([
            'tank_reading_after' => 'required|numeric|gte:0',
            'truck_meter_reading' => 'required|numeric|gte:0',
            'tank_photo_after' => 'nullable|image|max:10240',
            'truck_photo' => 'nullable|image|max:10240',
        ]);

        if ($gasDelivery->status !== 'receiving') {
            return response()->json(['success' => false, 'message' => 'Esta recepción no está en estado de recepción.'], 400);
        }

        $tankSetting = GasTankSetting::getForCondominium($gasDelivery->condominium_id);
        $gallonsDelivered = (float) $validated['truck_meter_reading'];

        if ((float) $validated['tank_reading_after'] > $tankSetting->capacity_gallons) {
            $gasDelivery->update([
                'tank_reading_after' => $validated['tank_reading_after'],
                'truck_meter_reading' => $validated['truck_meter_reading'],
                'gallons_delivered' => $gallonsDelivered,
                'delivery_date' => now()->toDateString(),
                'notes' => ($gasDelivery->notes ? $gasDelivery->notes . ' | ' : '') . 'Advertencia: Lectura después excede capacidad del tanque (' . $tankSetting->capacity_gallons . ' gal).',
            ]);
        } else {
            $gasDelivery->update([
                'tank_reading_after' => $validated['tank_reading_after'],
                'truck_meter_reading' => $validated['truck_meter_reading'],
                'gallons_delivered' => $gallonsDelivered,
                'delivery_date' => now()->toDateString(),
            ]);
        }

        if ($request->hasFile('tank_photo_after')) {
            $path = $request->file('tank_photo_after')->store('gas-deliveries', 'public');
            $gasDelivery->update(['tank_photo_after_path' => $path]);
        }

        if ($request->hasFile('truck_photo')) {
            $path = $request->file('truck_photo')->store('gas-deliveries', 'public');
            $gasDelivery->update(['truck_photo_path' => $path]);
        }

        $this->auditLog->log('gas_delivery_receiving_updated', 'gas', $gasDelivery->id);

        return response()->json([
            'success' => true,
            'message' => 'Lecturas del camión y tanque registradas.',
            'data' => [
                'id' => $gasDelivery->id,
                'status' => $gasDelivery->fresh()->status,
                'gallons_delivered' => (float) $gasDelivery->gallons_delivered,
            ],
        ]);
    }

    public function completeGasDelivery(Request $request, GasDelivery $gasDelivery)
    {
        $validated = $request->validate([
            'invoice_amount' => 'required|numeric|gt:0',
            'invoice_photo' => 'nullable|image|max:10240',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($gasDelivery->status !== 'receiving') {
            return response()->json(['success' => false, 'message' => 'Esta recepción no está en estado de recepción.'], 400);
        }

        if (!$gasDelivery->tank_reading_after || !$gasDelivery->gallons_delivered) {
            return response()->json(['success' => false, 'message' => 'Debe completar el paso 2 (lecturas del tanque y camión) antes de finalizar.'], 400);
        }

        $gasDelivery->update([
            'invoice_amount' => $validated['invoice_amount'],
            'status' => 'completed',
            'notes' => $validated['notes'] ?? $gasDelivery->notes,
        ]);

        if ($request->hasFile('invoice_photo')) {
            $path = $request->file('invoice_photo')->store('gas-deliveries', 'public');
            $gasDelivery->update(['invoice_photo_path' => $path]);
        }

        $this->createExpenseForDelivery($gasDelivery);

        $tankSetting = GasTankSetting::getForCondominium($gasDelivery->condominium_id);
        $tankSetting->update([
            'last_reading' => $gasDelivery->tank_reading_after,
            'last_reading_date' => $gasDelivery->delivery_date ?? now(),
        ]);

        $this->auditLog->log('gas_delivery_completed', 'gas', $gasDelivery->id);

        return response()->json([
            'success' => true,
            'message' => 'Recepción de gas completada correctamente.',
            'data' => [
                'id' => $gasDelivery->id,
                'status' => 'completed',
                'invoice_amount' => (float) $gasDelivery->invoice_amount,
                'gallons_delivered' => (float) $gasDelivery->gallons_delivered,
            ],
        ]);
    }

    public function gasDeliveryList(Request $request)
    {
        $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
        ]);

        $user = $request->user();
        if ($user->role === 'admin' && (int) $request->condominium_id !== $user->condominium_id) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $deliveries = GasDelivery::where('condominium_id', $request->condominium_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'delivery_date' => $d->delivery_date?->format('Y-m-d'),
                'tank_reading_before' => (float) ($d->tank_reading_before ?? 0),
                'tank_reading_after' => (float) ($d->tank_reading_after ?? 0),
                'truck_meter_reading' => (float) ($d->truck_meter_reading ?? 0),
                'gallons_delivered' => (float) ($d->gallons_delivered ?? 0),
                'invoice_amount' => (float) ($d->invoice_amount ?? 0),
                'status' => $d->status,
                'tank_photo_before_url' => $d->tank_photo_before_path ? Storage::url($d->tank_photo_before_path) : null,
                'tank_photo_after_url' => $d->tank_photo_after_path ? Storage::url($d->tank_photo_after_path) : null,
                'truck_photo_url' => $d->truck_photo_path ? Storage::url($d->truck_photo_path) : null,
                'invoice_photo_url' => $d->invoice_photo_path ? Storage::url($d->invoice_photo_path) : null,
                'notes' => $d->notes,
                'created_at' => $d->created_at->format('Y-m-d H:i:s'),
            ]);

        return response()->json([
            'success' => true,
            'data' => $deliveries,
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

    private function createExpenseForDelivery(GasDelivery $gasDelivery): void
    {
        if (!$gasDelivery->invoice_amount || $gasDelivery->invoice_amount <= 0) {
            return;
        }

        $category = ExpenseCategory::firstOrCreate(
            [
                'condominium_id' => $gasDelivery->condominium_id,
                'name' => 'Combustible',
            ],
            ['status' => 'active']
        );

        Expense::create([
            'condominium_id' => $gasDelivery->condominium_id,
            'category_id' => $category->id,
            'date' => $gasDelivery->delivery_date ?? now()->toDateString(),
            'concept' => 'Compra de gas - ' . number_format($gasDelivery->gallons_delivered ?? 0, 2) . ' galones',
            'amount' => $gasDelivery->invoice_amount,
            'receipt_path' => $gasDelivery->invoice_photo_path,
            'created_by' => $gasDelivery->created_by,
        ]);
    }
}