<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    /**
     * Crea fattura da pagamento
     *
     * INTEGRATION: Usa billing accessors da Task #4 (gestione minori)
     *
     * @param Payment $payment
     * @return Invoice
     */
    public function createFromPayment(Payment $payment): Invoice
    {
        $student = $payment->user; // studente
        $school = $payment->school;

        // Crea invoice con billing info (usa accessor da Task #4)
        $invoice = Invoice::create([
            'school_id' => $school->id,
            'payment_id' => $payment->id,
            'user_id' => $student->id,
            'amount' => $payment->amount,
            'invoice_date' => now(),
            'description' => $this->buildDescription($payment),

            // Snapshot billing data (usa accessor da Task #4)
            'billing_name' => $student->billing_name ?? $student->full_name,
            'billing_fiscal_code' => $student->billing_fiscal_code ?? $student->codice_fiscale,
            'billing_email' => $student->contact_email ?? $student->email,
            'billing_address' => $this->formatAddress($student),

            'status' => 'issued'
        ]);

        Log::info('Invoice created from payment', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'payment_id' => $payment->id,
            'school_id' => $school->id,
            'amount' => $invoice->amount
        ]);

        return $invoice;
    }

    /**
     * Genera PDF e salva su storage
     *
     * INTEGRATION: Usa settings da Task #8 (ricevute) e Task #9 (logo upload)
     *
     * @param Invoice $invoice
     * @return string PDF path
     * @throws \Exception
     */
    public function generatePDF(Invoice $invoice): string
    {
        $school = $invoice->school;

        // Carica settings ricevute (Task #8, #9)
        $settings = [
            'logo_path' => Setting::get("school.{$school->id}.receipt.logo_path"),
            'logo_url' => Setting::get("school.{$school->id}.receipt.logo_url"),
            'show_logo' => Setting::get("school.{$school->id}.receipt.show_logo", true),
            'header_text' => Setting::get("school.{$school->id}.receipt.header_text"),
            'footer_text' => Setting::get("school.{$school->id}.receipt.footer_text"),
            'school_name' => Setting::get("school.{$school->id}.name", $school->name),
            'school_address' => Setting::get("school.{$school->id}.address"),
            'school_city' => Setting::get("school.{$school->id}.city"),
            'school_postal_code' => Setting::get("school.{$school->id}.postal_code"),
            'school_vat_number' => Setting::get("school.{$school->id}.vat_number"),
            'school_tax_code' => Setting::get("school.{$school->id}.tax_code"),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('admin.invoices.pdf', [
            'invoice' => $invoice,
            'settings' => $settings
        ]);

        // Save to storage
        $filename = "invoice_{$invoice->invoice_number}_{$invoice->id}.pdf";
        $path = "invoices/{$school->id}/{$filename}";

        // Ensure directory exists
        $fullPath = storage_path('app/' . dirname($path));
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        Storage::disk('local')->put($path, $pdf->output());

        // Update invoice with path
        $invoice->update(['pdf_path' => $path]);

        Log::info('Invoice PDF generated', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'path' => $path,
            'school_id' => $school->id
        ]);

        return $path;
    }

    /**
     * Build description for invoice based on payment type
     *
     * @param Payment $payment
     * @return string
     */
    private function buildDescription(Payment $payment): string
    {
        $description = 'Pagamento';

        if ($payment->payment_type) {
            $description .= " - {$payment->payment_type_name}";
        }

        if ($payment->course) {
            $description .= " per corso: {$payment->course->name}";
        } elseif ($payment->event) {
            $description .= " per evento: {$payment->event->name}";
        }

        if ($payment->notes) {
            $description .= " ({$payment->notes})";
        }

        return $description;
    }

    /**
     * Format student address for billing
     *
     * @param \App\Models\User $student
     * @return string|null
     */
    private function formatAddress($student): ?string
    {
        // Format indirizzo se disponibile
        $parts = array_filter([
            $student->address ?? null,
            $student->city ?? null,
            $student->postal_code ?? null
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
