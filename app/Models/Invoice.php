<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'payment_id',
        'user_id',
        'invoice_number',
        'amount',
        'invoice_date',
        'description',
        'billing_name',
        'billing_fiscal_code',
        'billing_email',
        'billing_address',
        'pdf_path',
        'status',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'sent_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    // RELATIONSHIPS

    /**
     * Ottiene la scuola che ha emesso la fattura
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Ottiene il pagamento associato alla fattura
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Ottiene lo studente (o genitore se minore) a cui è intestata la fattura
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // BOOT & AUTO-GENERATION

    /**
     * The "booted" method of the model.
     *
     * SENIOR FIX: Auto-genera invoice_number al momento della creazione
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->school_id);
            }
        });
    }

    /**
     * Genera numero fattura univoco formato YYYY-NNN
     *
     * Es: 2026-001, 2026-002, ...
     *
     * @param int $schoolId
     * @return string
     */
    public static function generateInvoiceNumber(int $schoolId): string
    {
        $year = now()->format('Y');

        // Trova l'ultima fattura dell'anno per questa scuola
        $lastInvoice = self::where('school_id', $schoolId)
            ->where('invoice_number', 'like', "{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        // Calcola sequence number
        $sequence = $lastInvoice
            ? intval(explode('-', $lastInvoice->invoice_number)[1]) + 1
            : 1;

        return sprintf('%s-%03d', $year, $sequence);
    }

    // ACCESSORS

    /**
     * Ottiene l'importo formattato con valuta
     */
    public function getFormattedAmountAttribute(): string
    {
        return '€ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Verifica se la fattura è stata inviata
     */
    public function getIsSentAttribute(): bool
    {
        return $this->status === 'sent' || !is_null($this->sent_at);
    }

    /**
     * Verifica se la fattura è pagata
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Verifica se la fattura è annullata
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    // HELPER METHODS

    /**
     * Marca la fattura come inviata
     */
    public function markAsSent(): bool
    {
        $this->status = 'sent';
        $this->sent_at = now();
        return $this->save();
    }

    /**
     * Marca la fattura come pagata
     */
    public function markAsPaid(): bool
    {
        $this->status = 'paid';
        return $this->save();
    }

    /**
     * Annulla la fattura
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }
}
