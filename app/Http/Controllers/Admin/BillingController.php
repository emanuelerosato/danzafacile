<?php

namespace App\Http\Controllers\Admin;

use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * TASK #11: Billing Controller - Storage Upgrade
 */
class BillingController extends AdminBaseController
{
    protected StorageQuotaService $storageQuotaService;

    public function __construct(StorageQuotaService $storageQuotaService)
    {
        parent::__construct();
        $this->storageQuotaService = $storageQuotaService;
    }

    /**
     * Show storage upgrade page
     */
    public function storage()
    {
        $this->setupContext();

        $storageInfo = $this->storageQuotaService->getStorageInfo($this->school);

        // Pricing plans
        $plans = [
            [
                'name' => 'Piano Base',
                'gb' => 5,
                'price' => 0,
                'type' => 'base',
                'features' => ['5GB storage', 'Supporto email'],
            ],
            [
                'name' => 'Piano Plus',
                'gb' => 20,
                'price' => 9.99,
                'type' => 'monthly',
                'features' => ['20GB storage', 'Supporto prioritario', 'Backup automatico'],
            ],
            [
                'name' => 'Piano Pro',
                'gb' => 50,
                'price' => 19.99,
                'type' => 'monthly',
                'features' => ['50GB storage', 'Supporto 24/7', 'Backup automatico', 'Logo personalizzato'],
            ],
            [
                'name' => 'Piano Unlimited',
                'gb' => null, // Unlimited
                'price' => 49.99,
                'type' => 'monthly',
                'features' => ['Storage illimitato', 'Supporto dedicato', 'Backup automatico', 'Tutti i premium features'],
            ],
        ];

        return view('admin.billing.storage', compact('storageInfo', 'plans'));
    }

    /**
     * Purchase additional storage
     */
    public function purchaseStorage(Request $request)
    {
        $this->setupContext();

        $request->validate([
            'plan_type' => 'required|in:plus,pro,unlimited',
            'payment_method' => 'required|in:paypal,stripe',
        ]);

        $planType = $request->input('plan_type');

        // Map plan to GB
        $gbMap = [
            'plus' => 20,
            'pro' => 50,
            'unlimited' => null,
        ];

        try {
            if ($planType === 'unlimited') {
                // Enable unlimited storage
                $this->storageQuotaService->enableUnlimited($this->school);

                Log::info('Unlimited storage purchased', [
                    'school_id' => $this->school->id,
                    'admin_id' => auth()->id(),
                    'payment_method' => $request->input('payment_method')
                ]);

                return redirect()->back()
                    ->with('success', 'Storage illimitato attivato con successo!');

            } else {
                // Purchase additional GB
                $newQuotaGB = $gbMap[$planType];
                $additionalGB = $newQuotaGB - $this->school->storage_quota_gb;

                if ($additionalGB <= 0) {
                    return redirect()->back()
                        ->with('error', 'Hai già questo piano o superiore.');
                }

                $this->storageQuotaService->purchaseAdditionalStorage(
                    $this->school,
                    $additionalGB,
                    true // Temporary = scade dopo 1 anno
                );

                Log::info('Additional storage purchased', [
                    'school_id' => $this->school->id,
                    'admin_id' => auth()->id(),
                    'plan_type' => $planType,
                    'additional_gb' => $additionalGB,
                    'new_quota_gb' => $newQuotaGB,
                    'payment_method' => $request->input('payment_method')
                ]);

                return redirect()->back()
                    ->with('success', "Piano {$planType} attivato! +{$additionalGB}GB aggiunti.");
            }

        } catch (\Exception $e) {
            Log::error('Storage purchase failed', [
                'school_id' => $this->school->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore durante l\'acquisto. Riprova o contatta il supporto.');
        }
    }
}
