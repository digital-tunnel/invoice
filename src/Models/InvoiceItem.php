<?php

namespace DigitalTunnel\Invoice\Models;

use DigitalTunnel\Invoice\Classes\PdfInvoiceItem;
use Exception;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        /**
         * This cast will be forwarded to the class defined in config at invoices.money_cast
         */
        'metadata' => AsArrayObject::class,
        'tax_percentage' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(config('invoices.model_invoice'));
    }

    /**
     * @throws Exception
     */
    public function toPdfInvoiceItem(): PdfInvoiceItem
    {
        return new PdfInvoiceItem(
            label: $this->label,
            unit_price: $this->unit_price,
            unit_tax: $this->unit_tax,
            tax_percentage: $this->tax_percentage,
            quantity: $this->quantity,
            currency: $this->currency,
            description: $this->description,
        );
    }
}
