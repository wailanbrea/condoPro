<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\ApartmentUser;
use App\Models\BankAccount;
use App\Models\BillItem;
use App\Models\Condominium;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExtraCharge;
use App\Models\ExtraChargeApartment;
use App\Models\ExtraChargeInstallment;
use App\Models\FinancialMovement;
use App\Models\GasReading;
use App\Models\MonthlyBill;
use App\Models\MonthlyFinancialReport;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $condo = Condominium::create([
            'name' => 'Residencial Natalie XIII',
            'address' => 'Calle Principal #13, Ensanche Natalie, Santo Domingo',
            'phone' => '809-555-1313',
            'email' => 'administracion@natalie13.com.do',
            'currency' => 'DOP',
            'language_default' => 'es',
            'gas_price_per_gallon' => 147.20,
            'gas_conversion_factor' => 1.20,
            'status' => 'active',
        ]);

        $superAdmin = User::create([
            'name' => 'Administrador General',
            'email' => 'admin@condopro.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $carlosAdmin = User::create([
            'name' => 'Carlos Medina',
            'email' => 'carlos@natalie13.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'condominium_id' => $condo->id,
            'phone' => '809-555-0001',
            'status' => 'active',
        ]);

        $karina = User::create(['name' => 'Karina Feliz', 'email' => 'karina@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1001', 'status' => 'active']);
        $rocio = User::create(['name' => 'Rocio Santos', 'email' => 'rocio@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1002', 'status' => 'active']);
        $daniel = User::create(['name' => 'Daniel Cruz', 'email' => 'daniel@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1003', 'status' => 'active']);
        $maria = User::create(['name' => 'Maria Lopez', 'email' => 'maria@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1004', 'status' => 'active']);
        $jose = User::create(['name' => 'Jose Ramirez', 'email' => 'jose@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1005', 'status' => 'active']);
        $cynthia = User::create(['name' => 'Cynthia Paulino', 'email' => 'cynthia@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1006', 'status' => 'active']);
        $evelyn = User::create(['name' => 'Evelyn Valenzuela', 'email' => 'evelyn@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1007', 'status' => 'active']);
        $marino = User::create(['name' => 'Marino De La Rosa', 'email' => 'marino@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1008', 'status' => 'active']);
        $ana = User::create(['name' => 'Ana Rodriguez', 'email' => 'ana@gmail.com', 'password' => Hash::make('password'), 'role' => 'resident', 'condominium_id' => $condo->id, 'phone' => '809-555-1009', 'status' => 'active']);

        // 10 apartments, all with gas meter, flat maintenance RD$7,800
        $apt1A = Apartment::create(['condominium_id' => $condo->id, 'number' => '1A', 'owner_name' => 'Karina Feliz', 'area' => 70.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt1B = Apartment::create(['condominium_id' => $condo->id, 'number' => '1B', 'owner_name' => 'Rocio Santos', 'area' => 72.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt2A = Apartment::create(['condominium_id' => $condo->id, 'number' => '2A', 'owner_name' => 'Daniel Cruz', 'area' => 68.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt2B = Apartment::create(['condominium_id' => $condo->id, 'number' => '2B', 'owner_name' => 'Maria Lopez', 'area' => 72.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt3A = Apartment::create(['condominium_id' => $condo->id, 'number' => '3A', 'owner_name' => 'Jose Ramirez', 'area' => 75.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt3B = Apartment::create(['condominium_id' => $condo->id, 'number' => '3B', 'owner_name' => 'Cynthia Paulino', 'area' => 65.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt4A = Apartment::create(['condominium_id' => $condo->id, 'number' => '4A', 'owner_name' => 'Evelyn Valenzuela', 'area' => 70.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt4B = Apartment::create(['condominium_id' => $condo->id, 'number' => '4B', 'owner_name' => 'Marino De La Rosa', 'area' => 68.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $apt5A = Apartment::create(['condominium_id' => $condo->id, 'number' => '5A', 'owner_name' => 'Ana Rodriguez', 'area' => 80.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);
        $aptPH = Apartment::create(['condominium_id' => $condo->id, 'number' => 'PH-1', 'owner_name' => 'Carlos Medina', 'area' => 110.00, 'maintenance_fee' => 7800, 'balance' => 0, 'has_gas_meter' => true, 'status' => 'active']);

        $allApts = [$apt1A, $apt1B, $apt2A, $apt2B, $apt3A, $apt3B, $apt4A, $apt4B, $apt5A, $aptPH];

        ApartmentUser::create(['apartment_id' => $apt1A->id, 'user_id' => $karina->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt1B->id, 'user_id' => $rocio->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt2A->id, 'user_id' => $daniel->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt2B->id, 'user_id' => $maria->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt3A->id, 'user_id' => $jose->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt3B->id, 'user_id' => $cynthia->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt4A->id, 'user_id' => $evelyn->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt4B->id, 'user_id' => $marino->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $apt5A->id, 'user_id' => $ana->id, 'is_primary' => true]);
        ApartmentUser::create(['apartment_id' => $aptPH->id, 'user_id' => $carlosAdmin->id, 'is_primary' => true]);

        $bpPopular = BankAccount::create([
            'condominium_id' => $condo->id, 'bank_name' => 'Banco Popular Dominicano',
            'account_holder' => 'Residencial Natalie XIII', 'account_number' => '809-7654321-0',
            'account_type' => 'savings', 'currency' => 'DOP', 'status' => 'active',
        ]);
        $banReservas = BankAccount::create([
            'condominium_id' => $condo->id, 'bank_name' => 'BanReservas',
            'account_holder' => 'Residencial Natalie XIII', 'account_number' => '960-1122334-5',
            'account_type' => 'checking', 'currency' => 'DOP', 'status' => 'active',
        ]);

        $catServicios = ExpenseCategory::create(['condominium_id' => $condo->id, 'name' => 'Servicios Públicos', 'status' => 'active']);
        $catSeguridad = ExpenseCategory::create(['condominium_id' => $condo->id, 'name' => 'Seguridad', 'status' => 'active']);
        $catMantenimiento = ExpenseCategory::create(['condominium_id' => $condo->id, 'name' => 'Mantenimiento', 'status' => 'active']);
        $catAdministracion = ExpenseCategory::create(['condominium_id' => $condo->id, 'name' => 'Administración', 'status' => 'active']);

        $now = now();
        $prev1 = $now->copy()->subMonthNoOverflow();
        $prev2 = $now->copy()->subMonths(2);

        // === EXTRA CHARGE - Pintura del edificio (RD$120,000 / 10 apts / 3 meses = RD$4,000/mes) ===
        $pintura = ExtraCharge::create([
            'condominium_id' => $condo->id, 'title' => 'Pintura del edificio',
            'description' => 'Pintura completa de la fachada y áreas comunes del edificio', 'total_amount' => 120000,
            'distribution_type' => 'equal', 'start_month' => $now->month, 'start_year' => $now->year,
            'installments_count' => 3, 'status' => 'active', 'created_by' => $carlosAdmin->id,
        ]);
        foreach ($allApts as $apt) {
            ExtraChargeApartment::create(['extra_charge_id' => $pintura->id, 'apartment_id' => $apt->id, 'assigned_amount' => 12000, 'monthly_amount' => 4000, 'percentage' => 10]);
            for ($i = 0; $i < 3; $i++) {
                $m = $now->month + $i;
                $y = $now->year;
                if ($m > 12) { $m -= 12; $y++; }
                ExtraChargeInstallment::create(['extra_charge_id' => $pintura->id, 'apartment_id' => $apt->id, 'billing_month' => $m, 'billing_year' => $y, 'amount' => 4000, 'status' => 'pending']);
            }
        }

        // === GAS READINGS - Mayo 2026 (exact values from spec) ===
        $gasData = [
            [$apt1A, 'G-001A', 1850.350, 1880.600, 30.250],
            [$apt1B, 'G-001B', 1650.100, 1672.300, 22.200],
            [$apt2A, 'G-002A', 2084.130, 2101.750, 17.620],
            [$apt2B, 'G-002B', 1930.400, 1952.600, 22.200],
            [$apt3A, 'G-003A', 1750.250, 1775.500, 25.250],
            [$apt3B, 'G-003B', 1921.490, 1936.770, 15.280],
            [$apt4A, 'G-004A', 2164.520, 2184.330, 19.810],
            [$apt4B, 'G-004B', 1820.000, 1848.700, 28.700],
            [$apt5A, 'G-005A', 2510.100, 2555.350, 45.250],
            [$aptPH, 'G-PH1', 3200.750, 3260.400, 59.650],
        ];

        $gasReadings = [];
        foreach ($gasData as [$apt, $meter, $initial, $final, $consumption]) {
            $gallons = round($consumption * $condo->gas_conversion_factor, 2);
            $totalGas = round($gallons * $condo->gas_price_per_gallon, 2);
            $reading = GasReading::create([
                'condominium_id' => $condo->id,
                'apartment_id' => $apt->id,
                'meter_number' => $meter,
                'reading_date_start' => $now->copy()->startOfMonth(),
                'reading_date_end' => $now->copy()->endOfMonth(),
                'reading_initial' => $initial,
                'reading_final' => $final,
                'consumption_m3' => $consumption,
                'conversion_factor' => $condo->gas_conversion_factor,
                'gallons' => $gallons,
                'price_per_gallon' => $condo->gas_price_per_gallon,
                'gallon_price' => $condo->gas_price_per_gallon,
                'extra_cost_per_gallon' => 0,
                'total_gallon_price' => $condo->gas_price_per_gallon,
                'total_gas' => $totalGas,
                'total_amount' => $totalGas,
                'billing_month' => $now->month,
                'billing_year' => $now->year,
                'billed' => false,
                'created_by' => $carlosAdmin->id,
                'status' => 'active',
            ]);
            $gasReadings[$apt->number] = $reading;
        }

        // === BILLS - Mayo 2026 ===
        $billData = [
            ['apt' => $apt1A, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 30.250 m³ × 1.20 = 36.30 gal × RD$147.20 = RD$5,343.96', 5343.96, $gasReadings['1A']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt1B, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 22.200 m³ × 1.20 = 26.64 gal × RD$147.20 = RD$3,922.08', 3922.08, $gasReadings['1B']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt2A, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 17.620 m³ × 1.20 = 21.14 gal × RD$147.20 = RD$3,112.40', 3112.40, $gasReadings['2A']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt2B, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 22.200 m³ × 1.20 = 26.64 gal × RD$147.20 = RD$3,922.08', 3922.08, $gasReadings['2B']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt3A, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 25.250 m³ × 1.20 = 30.30 gal × RD$147.20 = RD$4,460.16', 4460.16, $gasReadings['3A']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt3B, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 15.280 m³ × 1.20 = 18.34 gal × RD$147.20 = RD$2,699.24', 2699.24, $gasReadings['3B']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt4A, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 19.810 m³ × 1.20 = 23.77 gal × RD$147.20 = RD$3,499.42', 3499.42, $gasReadings['4A']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt4B, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 28.700 m³ × 1.20 = 34.44 gal × RD$147.20 = RD$5,071.68', 5071.68, $gasReadings['4B']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $apt5A, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 45.250 m³ × 1.20 = 54.30 gal × RD$147.20 = RD$7,992.96', 7992.96, $gasReadings['5A']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
            ['apt' => $aptPH, 'items' => [['maintenance', 'Cuota Mantenimiento', 7800, null], ['gas', 'Gas: 59.650 m³ × 1.20 = 71.58 gal × RD$147.20 = RD$10,536.58', 10536.58, $gasReadings['PH-1']->id], ['extra_charge', 'Pintura del edificio', 4000, $pintura->id]]],
        ];

        $bills = [];
        foreach ($billData as $bd) {
            $subtotal = array_sum(array_column($bd['items'], 2));
            $bill = MonthlyBill::create([
                'condominium_id' => $condo->id, 'apartment_id' => $bd['apt']->id,
                'billing_month' => $now->month, 'billing_year' => $now->year,
                'subtotal' => $subtotal, 'previous_balance' => 0, 'payments_applied' => 0, 'total' => $subtotal,
                'status' => 'pending',
                'due_date' => $now->copy()->endOfMonth()->format('Y-m-d'),
            ]);
            foreach ($bd['items'] as $item) {
                BillItem::create(['bill_id' => $bill->id, 'concept_type' => $item[0], 'description' => $item[1], 'amount' => $item[2], 'reference_id' => $item[3]]);
            }
            $bills[$bd['apt']->number] = $bill;
        }

        // Mark gas readings as billed
        GasReading::where('condominium_id', $condo->id)->where('billing_month', $now->month)->where('billing_year', $now->year)->update(['billed' => true]);

        // === PAYMENTS - Round 1: Full payments (8 apartments) + 2 partial ===
        $voucherPool = ['vouchers/voucher_sample.png', 'vouchers/voucher_sample2.png', 'vouchers/voucher_sample3.png', 'vouchers/voucher_sample4.png'];

        $fullPayApts = [$apt1A, $apt1B, $apt2A, $apt3A, $apt3B, $apt4A, $apt5A, $aptPH];
        foreach ($fullPayApts as $i => $apt) {
            $bill = $bills[$apt->number];
            Payment::create([
                'condominium_id' => $condo->id, 'apartment_id' => $apt->id,
                'user_id' => $apt->users()->first()->id,
                'bill_id' => $bill->id, 'bank_account_id' => $bpPopular->id,
                'amount' => $bill->total,
                'payment_date' => $now->copy()->subDays(rand(1, 10)),
                'reference_number' => 'REF-' . rand(100000, 999999),
                'voucher_path' => $voucherPool[$i % count($voucherPool)],
                'status' => 'confirmed', 'confirmed_by' => $carlosAdmin->id, 'confirmed_at' => $now->copy()->subDays(rand(0, 5)),
            ]);
            $bill->payments_applied = $bill->total;
            $bill->status = 'paid';
            $bill->save();
        }

        // 2B - partial payment RD$10,000
        $bill2B = $bills['2B'];
        Payment::create([
            'condominium_id' => $condo->id, 'apartment_id' => $apt2B->id,
            'user_id' => $maria->id, 'bill_id' => $bill2B->id, 'bank_account_id' => $banReservas->id,
            'amount' => 10000, 'payment_date' => $now->copy()->subDays(5),
            'reference_number' => 'REF-200001', 'voucher_path' => 'vouchers/voucher_sample.png',
            'status' => 'confirmed', 'confirmed_by' => $carlosAdmin->id, 'confirmed_at' => $now->copy()->subDays(3),
        ]);
        $bill2B->payments_applied = 10000;
        $bill2B->status = 'partial';
        $bill2B->save();

        // 4B - partial payment RD$5,000
        $bill4B = $bills['4B'];
        Payment::create([
            'condominium_id' => $condo->id, 'apartment_id' => $apt4B->id,
            'user_id' => $marino->id, 'bill_id' => $bill4B->id, 'bank_account_id' => $bpPopular->id,
            'amount' => 5000, 'payment_date' => $now->copy()->subDays(4),
            'reference_number' => 'REF-200002', 'voucher_path' => 'vouchers/voucher_sample2.png',
            'status' => 'confirmed', 'confirmed_by' => $carlosAdmin->id, 'confirmed_at' => $now->copy()->subDays(2),
        ]);
        $bill4B->payments_applied = 5000;
        $bill4B->status = 'partial';
        $bill4B->save();

        // === PAYMENTS - Round 2: Remaining payments to close month perfectly ===
        $remaining2B = round($bill2B->total - $bill2B->payments_applied, 2);
        Payment::create([
            'condominium_id' => $condo->id, 'apartment_id' => $apt2B->id,
            'user_id' => $maria->id, 'bill_id' => $bill2B->id, 'bank_account_id' => $banReservas->id,
            'amount' => $remaining2B, 'payment_date' => $now->copy()->subDays(1),
            'reference_number' => 'REF-200003', 'voucher_path' => 'vouchers/voucher_sample3.png',
            'status' => 'confirmed', 'confirmed_by' => $carlosAdmin->id, 'confirmed_at' => $now->copy()->subDays(0),
        ]);
        $bill2B->payments_applied = $bill2B->total;
        $bill2B->status = 'paid';
        $bill2B->save();

        $remaining4B = round($bill4B->total - $bill4B->payments_applied, 2);
        Payment::create([
            'condominium_id' => $condo->id, 'apartment_id' => $apt4B->id,
            'user_id' => $marino->id, 'bill_id' => $bill4B->id, 'bank_account_id' => $bpPopular->id,
            'amount' => $remaining4B, 'payment_date' => $now->copy()->subDays(1),
            'reference_number' => 'REF-200004', 'voucher_path' => 'vouchers/voucher_sample4.png',
            'status' => 'confirmed', 'confirmed_by' => $carlosAdmin->id, 'confirmed_at' => $now->copy()->subDays(0),
        ]);
        $bill4B->payments_applied = $bill4B->total;
        $bill4B->status = 'paid';
        $bill4B->save();

        // === EXPENSES - Mayo 2026 (RD$109,300 total) ===
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catAdministracion->id, 'date' => $now->copy()->setDate($now->year, $now->month, 1), 'concept' => 'Nómina', 'amount' => 34000, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catServicios->id, 'date' => $now->copy()->setDate($now->year, $now->month, 3), 'concept' => 'CAASD - Agua potable', 'amount' => 5500, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catServicios->id, 'date' => $now->copy()->setDate($now->year, $now->month, 5), 'concept' => 'EDESUR - Energía eléctrica', 'amount' => 18200, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catServicios->id, 'date' => $now->copy()->setDate($now->year, $now->month, 8), 'concept' => 'Gas edificio', 'amount' => 32000, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catAdministracion->id, 'date' => $now->copy()->setDate($now->year, $now->month, 10), 'concept' => 'Ayuntamiento', 'amount' => 4500, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catMantenimiento->id, 'date' => $now->copy()->setDate($now->year, $now->month, 12), 'concept' => 'Detergentes', 'amount' => 3200, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catMantenimiento->id, 'date' => $now->copy()->setDate($now->year, $now->month, 15), 'concept' => 'Botellones', 'amount' => 600, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catMantenimiento->id, 'date' => $now->copy()->setDate($now->year, $now->month, 18), 'concept' => 'Reparación bomba', 'amount' => 8500, 'created_by' => $carlosAdmin->id]);
        Expense::create(['condominium_id' => $condo->id, 'category_id' => $catSeguridad->id, 'date' => $now->copy()->setDate($now->year, $now->month, 20), 'concept' => 'Internet cámaras', 'amount' => 2800, 'created_by' => $carlosAdmin->id]);

        // === FINANCIAL REPORT - Mayo 2026 ===
        $totalBilled = MonthlyBill::where('condominium_id', $condo->id)->where('billing_month', $now->month)->where('billing_year', $now->year)->sum('total');
        $totalGas = BillItem::where('concept_type', 'gas')->whereHas('bill', fn($q) => $q->where('condominium_id', $condo->id)->where('billing_month', $now->month)->where('billing_year', $now->year))->sum('amount');
        MonthlyFinancialReport::create([
            'condominium_id' => $condo->id,
            'month' => $now->month,
            'year' => $now->year,
            'initial_balance' => 40000,
            'total_income' => $totalBilled,
            'total_expenses' => 109300,
            'special_payments' => 0,
            'final_balance' => 40000 + $totalBilled - 109300,
            'total_maintenance' => 7800 * 10,
            'total_gas' => $totalGas,
            'total_extra_charges' => 4000 * 10,
            'total_pending' => 0,
            'status' => 'closed',
            'created_by' => $carlosAdmin->id,
            'closed_by' => $carlosAdmin->id,
            'closed_at' => $now->copy()->endOfMonth(),
            'notes' => 'Mes cerrado perfectamente - todos los pagos completos',
        ]);

        // === FINANCIAL MOVEMENTS (income from confirmed payments) ===
        foreach (MonthlyBill::where('condominium_id', $condo->id)->where('billing_month', $now->month)->where('billing_year', $now->year)->get() as $bill) {
            foreach ($bill->billItems as $item) {
                FinancialMovement::create([
                    'condominium_id' => $condo->id,
                    'movement_type' => 'income',
                    'category' => $item->concept_type === 'maintenance' ? 'maintenance' : ($item->concept_type === 'gas' ? 'gas' : 'extra_charge'),
                    'amount' => $item->amount,
                    'description' => $item->description . ' - Apt ' . $bill->apartment->number,
                    'movement_date' => $now->copy()->setDate($now->year, $now->month, rand(1, 20)),
                    'month' => $now->month,
                    'year' => $now->year,
                    'created_by' => $carlosAdmin->id,
                ]);
            }
        }

        foreach (Expense::where('condominium_id', $condo->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->get() as $expense) {
            FinancialMovement::create([
                'condominium_id' => $condo->id,
                'movement_type' => 'expense',
                'category' => strtolower($expense->category->name),
                'amount' => $expense->amount,
                'description' => $expense->concept,
                'movement_date' => $expense->date,
                'month' => $now->month,
                'year' => $now->year,
                'created_by' => $carlosAdmin->id,
            ]);
        }

        FinancialMovement::create([
            'condominium_id' => $condo->id,
            'movement_type' => 'adjustment',
            'category' => 'opening_balance',
            'amount' => 40000,
            'description' => 'Balance de apertura',
            'movement_date' => $now->copy()->startOfMonth(),
            'month' => $now->month,
            'year' => $now->year,
            'created_by' => $carlosAdmin->id,
        ]);

        // === Calculate real apartment balances (all paid, so all 0) ===
        $allApartments = Apartment::where('condominium_id', $condo->id)->get();
        foreach ($allApartments as $apt) {
            $outstandingBalance = MonthlyBill::where('apartment_id', $apt->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->get()
                ->sum(fn($bill) => max(0, $bill->total - $bill->payments_applied));

            $apt->balance = $outstandingBalance > 0 ? -$outstandingBalance : 0;
            $apt->save();
        }
    }
}