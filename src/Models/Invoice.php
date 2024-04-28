<?php

namespace DigitalTunnel\Invoice\Models;

use DigitalTunnel\Invoice\Casts\Discounts;
use DigitalTunnel\Invoice\Classes\InvoiceDiscount;
use DigitalTunnel\Invoice\Classes\PdfInvoice;
use DigitalTunnel\Invoice\Enums\InvoiceState;
use DigitalTunnel\Invoice\Enums\InvoiceType;
use Exception;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Mail\Attachment;

class Invoice extends Model implements Attachable
{
    protected $attributes = [
        'type' => InvoiceType::Invoice,
        'state' => InvoiceState::Draft,
    ];

    protected $guarded = [];

    protected $casts = [
        'type' => InvoiceType::class,
        'due_at' => 'datetime',
        'state' => InvoiceState::class,
        'seller_information' => AsArrayObject::class,
        'buyer_information' => AsArrayObject::class,
        'metadata' => AsArrayObject::class,
        'discounts' => Discounts::class,
    ];

    public static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            // prevent an invoice type if the parent is not set
            if ($invoice->type !== InvoiceType::Invoice && ! $invoice->parent_id) {
                throw new Exception('Credit or Quote invoice must have a parent invoice id', 500);
            }

            $invoice->serial_number = generateInvoiceNumber($invoice->type);
            $invoice->due_at = $invoice->due_at ?? now()->addDays(15); // @todo: make it configurable
        });

        static::created(function (Invoice $invoice) {
            // increment the count of the serial number
            incrementSerialNumber($invoice->type);
        });

        static::updating(function (Invoice $invoice) {
            if ($invoice->isDirty([
                'serial_number',
            ])) {
                throw new Exception('serial number cannot be updated after creation for integrity reasons', 500);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Any model that is the "parent" of the invoice like a Mission, a Transaction ...
     **/
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Typically, the buyer is one of your users, teams or any other model.
     * When editing your invoice, you should not rely on the information of this relation as they can change in time and impact all buyer's invoices.
     * Instead, you should store the buyer's information in his property on the invoice creation/validation.
     */
    public function buyer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * In case, your application is a marketplace; you would also attach the invoice to the seller
     * When editing your invoice. You should not rely on the information of this relation as they can change in time and impact all seller's invoices.
     * Instead, you should store the seller information in his property on the invoice creation/validation.
     */
    public function seller(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Invoice can be attached with another one
     * A Quote or a Credit can have another Invoice as a parent.
     * Ex: $invoice = $quote->parent and $quote = $invoice->quote
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Invoice::class, 'parent_id')->where('type', InvoiceType::Quote->value);
    }

    public function credit(): HasOne
    {
        return $this->hasOne(Invoice::class, 'parent_id')->where('type', InvoiceType::Credit->value);
    }

    /**
     * @return null|InvoiceDiscount[]
     */
    public function getDiscounts(): ?array
    {
        return $this->discounts;
    }

    public function scopeInvoice(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Invoice);
    }

    public function scopeCredit(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Credit);
    }

    public function scopeQuote(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Quote);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Paid);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Refunded);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Draft);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Pending);
    }

    /**
     * Get the attachable representation of the model.
     */
    public function toMailAttachment(): Attachment
    {
        return Attachment::fromData(fn () => $this->toPdfInvoice()->pdf()->output())
            ->as($this->toPdfInvoice()->getFilename())
            ->withMime('application/pdf');
    }

    public function toPdfInvoice(): PdfInvoice
    {
        return new PdfInvoice(
            name: $this->type->label(),
            state: $this->state->label(),
            serial_number: $this->serial_number,
            buyer: $this->buyer_information?->toArray(),
            due_at: $this->due_at,
            created_at: $this->created_at,
            paid_at: ($this->state === InvoiceState::Paid) ? $this->state_set_at : null,
            seller: $this->seller_information?->toArray(),
            description: $this->description,
            items: $this->items->map(/**
             * @throws Exception
             */ fn (InvoiceItem $item) => $item->toPdfInvoiceItem())->all(),
            tax_label: $this->getTaxLabel(),
            discounts: $this->getDiscounts()
        );
    }
}
