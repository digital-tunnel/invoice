<?php

namespace DigitalTunnel\Invoice\Enums;

enum InvoiceType: string
{
    case Invoice = 'invoice';
    case Quote = 'quote';
    case Credit = 'credit';
    case Proforma = 'proforma';

    public function label(): string
    {
        return match ($this) {
            self::Invoice => __('invoices::invoice.types.invoice'),
            self::Quote => __('invoices::invoice.types.quote'),
            self::Credit => __('invoices::invoice.types.credit'),
            self::Proforma => __('invoices::invoice.types.proforma'),
        };
    }
}
