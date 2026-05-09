<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function apartmentsByCondominium(int $condominium): JsonResponse
    {
        $user = Auth::user();

        if ($user->role === 'admin' && $condominium !== $user->condominium_id) {
            abort(403);
        }

        $apartments = Apartment::where('condominium_id', $condominium)
            ->where('status', 'active')
            ->orderBy('number')
            ->get(['id', 'number', 'maintenance_fee', 'area', 'has_gas_meter']);

        return response()->json($apartments);
    }

    public function gasApartmentsByCondominium(int $condominium): JsonResponse
    {
        $user = Auth::user();

        if ($user->role === 'admin' && $condominium !== $user->condominium_id) {
            abort(403);
        }

        $apartments = Apartment::where('condominium_id', $condominium)
            ->where('status', 'active')
            ->where('has_gas_meter', true)
            ->orderBy('number')
            ->get(['id', 'number', 'maintenance_fee', 'area', 'has_gas_meter']);

        return response()->json($apartments);
    }
}