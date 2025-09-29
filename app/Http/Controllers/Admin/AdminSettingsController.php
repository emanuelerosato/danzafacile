<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSettingsController extends Controller
{
    /**
     * Display the settings form
     */
    public function index()
    {
        $school = Auth::user()->school;

        // Stats
        $stats = [
            'total_settings' => 20, // Numero totale impostazioni configurabili
            'configured_settings' => $this->countConfiguredSettings($school),
            'paypal_status' => Setting::get("school.{$school->id}.paypal.enabled", false) ? 'active' : 'inactive',
            'receipt_configured' => !empty(Setting::get("school.{$school->id}.receipt.header_text", '')) || !empty(Setting::get("school.{$school->id}.receipt.footer_text", '')),
        ];

        // Get current settings values
        $settings = [
            // Company Information
            'school_name' => Setting::get("school.{$school->id}.name", $school->name),
            'school_address' => Setting::get("school.{$school->id}.address", ''),
            'school_city' => Setting::get("school.{$school->id}.city", ''),
            'school_postal_code' => Setting::get("school.{$school->id}.postal_code", ''),
            'school_country' => Setting::get("school.{$school->id}.country", 'Italia'),

            // Contact Information
            'school_phone' => Setting::get("school.{$school->id}.phone", ''),
            'school_email' => Setting::get("school.{$school->id}.email", ''),
            'school_website' => Setting::get("school.{$school->id}.website", ''),

            // Tax Information
            'school_vat_number' => Setting::get("school.{$school->id}.vat_number", ''),
            'school_tax_code' => Setting::get("school.{$school->id}.tax_code", ''),

            // Receipt Configuration
            'receipt_header_text' => Setting::get("school.{$school->id}.receipt.header_text", ''),
            'receipt_footer_text' => Setting::get("school.{$school->id}.receipt.footer_text", ''),
            'receipt_logo_url' => Setting::get("school.{$school->id}.receipt.logo_url", ''),
            'receipt_show_logo' => Setting::get("school.{$school->id}.receipt.show_logo", true),

            // Payment Terms
            'payment_terms' => Setting::get("school.{$school->id}.payment.terms", 'Pagamento da effettuare entro 30 giorni'),
            'payment_bank_details' => Setting::get("school.{$school->id}.payment.bank_details", ''),

            // Additional Notes
            'receipt_notes' => Setting::get("school.{$school->id}.receipt.notes", ''),

            // PayPal Configuration
            'paypal_enabled' => Setting::get("school.{$school->id}.paypal.enabled", false),
            'paypal_mode' => Setting::get("school.{$school->id}.paypal.mode", 'sandbox'),
            'paypal_currency' => Setting::get("school.{$school->id}.paypal.currency", 'EUR'),
            'paypal_client_id' => Setting::get("school.{$school->id}.paypal.client_id", ''),
            'paypal_client_secret' => Setting::get("school.{$school->id}.paypal.client_secret", ''),
            'paypal_fee_percentage' => Setting::get("school.{$school->id}.paypal.fee_percentage", '3.4'),
            'paypal_fixed_fee' => Setting::get("school.{$school->id}.paypal.fixed_fee", '0.35'),
        ];

        return view('admin.settings.index', compact('settings', 'school', 'stats'));
    }

    /**
     * Count configured settings
     */
    private function countConfiguredSettings($school)
    {
        $count = 0;
        $settingsKeys = [
            "school.{$school->id}.name",
            "school.{$school->id}.email",
            "school.{$school->id}.phone",
            "school.{$school->id}.address",
            "school.{$school->id}.vat_number",
            "school.{$school->id}.tax_code",
            "school.{$school->id}.receipt.header_text",
            "school.{$school->id}.receipt.footer_text",
            "school.{$school->id}.payment.terms",
            "school.{$school->id}.payment.bank_details",
        ];

        foreach ($settingsKeys as $key) {
            if (!empty(Setting::get($key, ''))) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Update the settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'nullable|string|max:500',
            'school_city' => 'nullable|string|max:100',
            'school_postal_code' => 'nullable|string|max:20',
            'school_country' => 'nullable|string|max:100',
            'school_phone' => 'nullable|string|max:50',
            'school_email' => 'nullable|email|max:255',
            'school_website' => 'nullable|url|max:255',
            'school_vat_number' => 'nullable|string|max:50',
            'school_tax_code' => 'nullable|string|max:50',
            'receipt_header_text' => 'nullable|string|max:1000',
            'receipt_footer_text' => 'nullable|string|max:1000',
            'receipt_logo_url' => 'nullable|url|max:500',
            'receipt_show_logo' => 'boolean',
            'payment_terms' => 'nullable|string|max:500',
            'payment_bank_details' => 'nullable|string|max:1000',
            'receipt_notes' => 'nullable|string|max:1000',

            // PayPal Validation Rules
            'paypal_enabled' => 'boolean',
            'paypal_mode' => 'required_if:paypal_enabled,1|in:sandbox,live',
            'paypal_currency' => 'required_if:paypal_enabled,1|in:EUR,USD,GBP',
            'paypal_client_id' => 'required_if:paypal_enabled,1|string|max:255',
            'paypal_client_secret' => 'required_if:paypal_enabled,1|string|max:255',
            'paypal_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'paypal_fixed_fee' => 'nullable|numeric|min:0',
        ]);

        $school = Auth::user()->school;

        // Save all settings with school-specific keys
        $settingsToSave = [
            // Company Information
            "school.{$school->id}.name" => ['value' => $request->school_name, 'type' => 'string'],
            "school.{$school->id}.address" => ['value' => $request->school_address, 'type' => 'string'],
            "school.{$school->id}.city" => ['value' => $request->school_city, 'type' => 'string'],
            "school.{$school->id}.postal_code" => ['value' => $request->school_postal_code, 'type' => 'string'],
            "school.{$school->id}.country" => ['value' => $request->school_country, 'type' => 'string'],

            // Contact Information
            "school.{$school->id}.phone" => ['value' => $request->school_phone, 'type' => 'string'],
            "school.{$school->id}.email" => ['value' => $request->school_email, 'type' => 'string'],
            "school.{$school->id}.website" => ['value' => $request->school_website, 'type' => 'string'],

            // Tax Information
            "school.{$school->id}.vat_number" => ['value' => $request->school_vat_number, 'type' => 'string'],
            "school.{$school->id}.tax_code" => ['value' => $request->school_tax_code, 'type' => 'string'],

            // Receipt Configuration
            "school.{$school->id}.receipt.header_text" => ['value' => $request->receipt_header_text, 'type' => 'string'],
            "school.{$school->id}.receipt.footer_text" => ['value' => $request->receipt_footer_text, 'type' => 'string'],
            "school.{$school->id}.receipt.logo_url" => ['value' => $request->receipt_logo_url, 'type' => 'string'],
            "school.{$school->id}.receipt.show_logo" => ['value' => $request->has('receipt_show_logo'), 'type' => 'boolean'],

            // Payment Terms
            "school.{$school->id}.payment.terms" => ['value' => $request->payment_terms, 'type' => 'string'],
            "school.{$school->id}.payment.bank_details" => ['value' => $request->payment_bank_details, 'type' => 'string'],

            // Additional Notes
            "school.{$school->id}.receipt.notes" => ['value' => $request->receipt_notes, 'type' => 'string'],

            // PayPal Configuration
            "school.{$school->id}.paypal.enabled" => ['value' => $request->has('paypal_enabled') && $request->paypal_enabled, 'type' => 'boolean'],
            "school.{$school->id}.paypal.mode" => ['value' => $request->paypal_mode ?? 'sandbox', 'type' => 'string'],
            "school.{$school->id}.paypal.currency" => ['value' => $request->paypal_currency ?? 'EUR', 'type' => 'string'],
            "school.{$school->id}.paypal.client_id" => ['value' => $request->paypal_client_id, 'type' => 'string'],
            "school.{$school->id}.paypal.client_secret" => ['value' => $request->paypal_client_secret, 'type' => 'string'],
            "school.{$school->id}.paypal.fee_percentage" => ['value' => $request->paypal_fee_percentage ?? '3.4', 'type' => 'string'],
            "school.{$school->id}.paypal.fixed_fee" => ['value' => $request->paypal_fixed_fee ?? '0.35', 'type' => 'string'],
        ];

        foreach ($settingsToSave as $key => $data) {
            Setting::set($key, $data['value'], $data['type']);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Impostazioni aggiornate con successo!');
    }
}