<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function log(string $action, string $module, ?int $referenceId = null, ?array $oldValue = null, ?array $newValue = null): AuditLog
    {
        $user = Auth::user();

        return AuditLog::create([
            'condominium_id' => $user?->condominium_id,
            'user_id' => $user?->id,
            'action' => $action,
            'module' => $module,
            'reference_id' => $referenceId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => Request::ip(),
        ]);
    }
}