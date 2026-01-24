<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminInvoiceController extends AdminBaseController
{
    /**
     * Download invoice PDF
     *
     * TASK #5: Download generated invoice PDF
     *
     * @param Invoice $invoice
     * @return Response
     */
    public function download(Invoice $invoice)
    {
        $this->setupContext();

        // Multi-tenant authorization
        if ($invoice->school_id !== $this->school->id) {
            abort(403, 'Non autorizzato ad accedere a questa fattura.');
        }

        // Check if PDF exists
        if (!$invoice->pdf_path || !Storage::disk('local')->exists($invoice->pdf_path)) {
            Log::error('Invoice PDF not found', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'pdf_path' => $invoice->pdf_path,
                'school_id' => $this->school->id
            ]);

            abort(404, 'PDF fattura non trovato. Rigenerare la fattura.');
        }

        Log::info('Invoice PDF downloaded', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'school_id' => $this->school->id,
            'admin_id' => auth()->id()
        ]);

        return Storage::disk('local')->download(
            $invoice->pdf_path,
            "Fattura_{$invoice->invoice_number}.pdf"
        );
    }
}
