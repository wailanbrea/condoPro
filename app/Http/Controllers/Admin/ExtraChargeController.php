<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Condominium;
use App\Models\ExtraCharge;
use App\Models\ExtraChargeApartment;
use App\Models\ExtraChargeInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExtraChargeController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $extraCharges = ExtraCharge::with('condominium', 'creator', 'extraChargeApartments.apartment')
            ->when($user->role === 'admin', fn($q) => $q->where('condominium_id', $user->condominium_id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.extra-charges.index', compact('extraCharges'));
    }

    public function create(): View
    {
        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.extra-charges.create', compact('condominiums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'distribution_type' => 'required|in:equal,by_area,custom',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year' => 'required|integer|min:2020',
            'installments_count' => 'required|integer|min:1',
            'apartment_ids' => 'nullable|array',
            'apartment_ids.*' => 'integer|exists:apartments,id',
            'custom_amounts' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
        ]);

        $this->authorizeCondo($validated['condominium_id']);

        $validated['created_by'] = Auth::id();

        DB::transaction(function () use ($validated) {
            $extraCharge = ExtraCharge::create($validated);

            $apartmentIds = $validated['apartment_ids'] ?? [];

            if (empty($apartmentIds)) {
                $apartmentIds = Apartment::where('condominium_id', $validated['condominium_id'])
                    ->where('status', 'active')
                    ->pluck('id')
                    ->toArray();
            }

            $apartments = Apartment::whereIn('id', $apartmentIds)->get();
            $count = $apartments->count();
            $totalArea = $apartments->sum('area') ?: 1;

            foreach ($apartments as $apartment) {
                $assignedAmount = 0;
                $apartmentPercentage = 0;

                if ($validated['distribution_type'] === 'equal') {
                    $apartmentPercentage = $count > 0 ? round(100 / $count, 2) : 0;
                    $assignedAmount = $count > 0 ? round($validated['total_amount'] / $count, 2) : 0;
                } elseif ($validated['distribution_type'] === 'by_area') {
                    $apartmentPercentage = $totalArea > 0 ? round(($apartment->area / $totalArea) * 100, 2) : 0;
                    $assignedAmount = round($validated['total_amount'] * ($apartmentPercentage / 100), 2);
                } elseif ($validated['distribution_type'] === 'custom') {
                    $customAmounts = $validated['custom_amounts'] ?? [];
                    $assignedAmount = isset($customAmounts[$apartment->id])
                        ? round((float) $customAmounts[$apartment->id], 2)
                        : round($validated['total_amount'] / $count, 2);
                }

                $apartmentMonthly = $validated['installments_count'] > 0
                    ? round($assignedAmount / $validated['installments_count'], 2)
                    : 0;

                ExtraChargeApartment::create([
                    'extra_charge_id' => $extraCharge->id,
                    'apartment_id' => $apartment->id,
                    'assigned_amount' => $assignedAmount,
                    'monthly_amount' => $apartmentMonthly,
                    'percentage' => $apartmentPercentage,
                ]);

                for ($i = 0; $i < $validated['installments_count']; $i++) {
                    $month = $validated['start_month'] + $i;
                    $year = $validated['start_year'];
                    while ($month > 12) {
                        $month -= 12;
                        $year++;
                    }

                    ExtraChargeInstallment::create([
                        'extra_charge_id' => $extraCharge->id,
                        'apartment_id' => $apartment->id,
                        'billing_month' => $month,
                        'billing_year' => $year,
                        'amount' => $apartmentMonthly,
                        'status' => 'pending',
                    ]);
                }
            }
        });

        return redirect()->route('extra-charges.index')
            ->with('success', __('messages.common.save') . '!');
    }

    public function show(ExtraCharge $extra_charge): View
    {
        $this->authorizeCondo($extra_charge->condominium_id);

        $extra_charge->load('condominium', 'creator', 'extraChargeApartments.apartment', 'extraChargeInstallments.apartment');

        return view('admin.extra-charges.show', compact('extra_charge'));
    }

    public function edit(ExtraCharge $extra_charge): View
    {
        $this->authorizeCondo($extra_charge->condominium_id);

        $condominiums = $this->getCondominiumsForSelect();

        return view('admin.extra-charges.edit', compact('extra_charge', 'condominiums'));
    }

    public function update(Request $request, ExtraCharge $extra_charge)
    {
        $this->authorizeCondo($extra_charge->condominium_id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'distribution_type' => 'required|in:equal,by_area,custom',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year' => 'required|integer|min:2020',
            'installments_count' => 'required|integer|min:1',
            'apartment_ids' => 'nullable|array',
            'apartment_ids.*' => 'integer|exists:apartments,id',
            'custom_amounts' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
        ]);

        DB::transaction(function () use ($validated, $extra_charge) {
            $extra_charge->update($validated);

            $extra_charge->extraChargeApartments()->delete();
            $extra_charge->extraChargeInstallments()->delete();

            $apartmentIds = $validated['apartment_ids'] ?? [];

            if (empty($apartmentIds)) {
                $apartmentIds = Apartment::where('condominium_id', $extra_charge->condominium_id)
                    ->where('status', 'active')
                    ->pluck('id')
                    ->toArray();
            }

            $apartments = Apartment::whereIn('id', $apartmentIds)->get();
            $count = $apartments->count();
            $totalArea = $apartments->sum('area') ?: 1;

            foreach ($apartments as $apartment) {
                $assignedAmount = 0;
                $apartmentPercentage = 0;

                if ($validated['distribution_type'] === 'equal') {
                    $apartmentPercentage = $count > 0 ? round(100 / $count, 2) : 0;
                    $assignedAmount = $count > 0 ? round($validated['total_amount'] / $count, 2) : 0;
                } elseif ($validated['distribution_type'] === 'by_area') {
                    $apartmentPercentage = $totalArea > 0 ? round(($apartment->area / $totalArea) * 100, 2) : 0;
                    $assignedAmount = round($validated['total_amount'] * ($apartmentPercentage / 100), 2);
                } elseif ($validated['distribution_type'] === 'custom') {
                    $customAmounts = $validated['custom_amounts'] ?? [];
                    $assignedAmount = isset($customAmounts[$apartment->id])
                        ? round((float) $customAmounts[$apartment->id], 2)
                        : round($validated['total_amount'] / $count, 2);
                }

                $apartmentMonthly = $validated['installments_count'] > 0
                    ? round($assignedAmount / $validated['installments_count'], 2)
                    : 0;

                ExtraChargeApartment::create([
                    'extra_charge_id' => $extra_charge->id,
                    'apartment_id' => $apartment->id,
                    'assigned_amount' => $assignedAmount,
                    'monthly_amount' => $apartmentMonthly,
                    'percentage' => $apartmentPercentage,
                ]);

                for ($i = 0; $i < $validated['installments_count']; $i++) {
                    $month = $validated['start_month'] + $i;
                    $year = $validated['start_year'];
                    while ($month > 12) {
                        $month -= 12;
                        $year++;
                    }

                    ExtraChargeInstallment::create([
                        'extra_charge_id' => $extra_charge->id,
                        'apartment_id' => $apartment->id,
                        'billing_month' => $month,
                        'billing_year' => $year,
                        'amount' => $apartmentMonthly,
                        'status' => 'pending',
                    ]);
                }
            }
        });

        return redirect()->route('extra-charges.show', $extra_charge)
            ->with('success', __('messages.common.save') . '!');
    }

    public function destroy(ExtraCharge $extra_charge)
    {
        $this->authorizeCondo($extra_charge->condominium_id);

        $extra_charge->extraChargeApartments()->delete();
        $extra_charge->extraChargeInstallments()->delete();
        $extra_charge->delete();

        return redirect()->route('extra-charges.index')
            ->with('success', __('messages.common.delete') . '!');
    }

    private function authorizeCondo(int $condominiumId): void
    {
        $user = Auth::user();
        if ($user->role === 'admin' && $condominiumId !== $user->condominium_id) {
            abort(403, __('messages.auth.unauthorized'));
        }
    }

    private function getCondominiumsForSelect()
    {
        $user = Auth::user();
        if ($user->role === 'super_admin') {
            return Condominium::orderBy('name')->pluck('name', 'id');
        }
        return Condominium::where('id', $user->condominium_id)->pluck('name', 'id');
    }
}