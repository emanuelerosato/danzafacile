<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StorageQuotaAuditLog;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * TASK #11 FASE 7: Super Admin Storage Management
 *
 * Gestisce le quote storage per tutte le scuole
 */
class SuperAdminSchoolStorageController extends Controller
{
    protected StorageQuotaService $storageQuotaService;

    public function __construct(StorageQuotaService $storageQuotaService)
    {
        $this->middleware(['auth', 'role:super_admin']);
        $this->storageQuotaService = $storageQuotaService;
    }

    /**
     * Dashboard overview storage tutte le scuole
     */
    public function index()
    {
        $schools = School::with(['mediaGalleries' => function ($query) {
            $query->select('id', 'school_id');
        }])
            ->select('id', 'name', 'storage_quota_gb', 'storage_used_bytes', 'storage_unlimited', 'storage_quota_expires_at', 'active')
            ->orderBy('storage_used_bytes', 'desc')
            ->paginate(20);

        // Calcola storage info per ogni scuola
        $schools->getCollection()->transform(function ($school) {
            $school->storage_info = $this->storageQuotaService->getStorageInfo($school);
            return $school;
        });

        return view('super-admin.schools.storage', compact('schools'));
    }

    /**
     * Update storage quota per una scuola
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'action' => 'required|in:add_quota,set_unlimited,reset_to_base',
            'additional_gb' => 'required_if:action,add_quota|integer|min:1|max:1000',
            'duration' => 'required_if:action,add_quota|in:permanent,1_year,6_months,3_months,custom',
            'custom_expiry_date' => 'required_if:duration,custom|date|after:today',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $action = $validated['action'];

        // Salva valori OLD per audit log
        $oldQuotaGb = $school->storage_quota_gb;
        $oldUnlimited = $school->storage_unlimited;
        $oldExpiresAt = $school->storage_quota_expires_at;

        try {
            switch ($action) {
                case 'add_quota':
                    $additionalGb = $validated['additional_gb'];
                    $newQuotaGb = $oldQuotaGb + $additionalGb;

                    // Calcola scadenza
                    $expiresAt = null;
                    if ($validated['duration'] !== 'permanent') {
                        $expiresAt = match ($validated['duration']) {
                            '1_year' => now()->addYear(),
                            '6_months' => now()->addMonths(6),
                            '3_months' => now()->addMonths(3),
                            'custom' => $validated['custom_expiry_date'],
                        };
                    }

                    $school->update([
                        'storage_quota_gb' => $newQuotaGb,
                        'storage_quota_expires_at' => $expiresAt,
                        'storage_unlimited' => false, // Reset unlimited se era attivo
                    ]);

                    $successMessage = "Aggiunti {$additionalGb}GB a {$school->name}. Nuova quota: {$newQuotaGb}GB";
                    break;

                case 'set_unlimited':
                    $this->storageQuotaService->enableUnlimited($school);

                    $school->update([
                        'storage_quota_expires_at' => null, // Nessuna scadenza per unlimited
                    ]);

                    $successMessage = "Storage illimitato attivato per {$school->name}";
                    break;

                case 'reset_to_base':
                    $school->update([
                        'storage_quota_gb' => 5, // Reset a base quota
                        'storage_unlimited' => false,
                        'storage_quota_expires_at' => null,
                    ]);

                    $successMessage = "Storage reset a quota base (5GB) per {$school->name}";
                    break;
            }

            // Refresh per ottenere valori aggiornati
            $school->refresh();

            // Crea audit log entry
            StorageQuotaAuditLog::create([
                'school_id' => $school->id,
                'super_admin_id' => auth()->id(),
                'action' => $action,
                'old_quota_gb' => $oldQuotaGb,
                'old_unlimited' => $oldUnlimited,
                'old_expires_at' => $oldExpiresAt,
                'new_quota_gb' => $school->storage_quota_gb,
                'new_unlimited' => $school->storage_unlimited,
                'new_expires_at' => $school->storage_quota_expires_at,
                'admin_note' => $validated['admin_note'] ?? null,
            ]);

            Log::info('Super Admin modified school storage quota', [
                'super_admin_id' => auth()->id(),
                'super_admin_name' => auth()->user()->name,
                'school_id' => $school->id,
                'school_name' => $school->name,
                'action' => $action,
                'old_quota_gb' => $oldQuotaGb,
                'new_quota_gb' => $school->storage_quota_gb,
            ]);

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Failed to update school storage quota', [
                'super_admin_id' => auth()->id(),
                'school_id' => $school->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Errore durante l\'aggiornamento. Riprova.');
        }
    }

    /**
     * Visualizza audit log per una scuola
     */
    public function auditLog(School $school)
    {
        $auditLogs = StorageQuotaAuditLog::where('school_id', $school->id)
            ->with('superAdmin:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('super-admin.schools.storage-audit-log', compact('school', 'auditLogs'));
    }
}
