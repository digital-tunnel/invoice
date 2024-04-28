<?php

use DigitalTunnel\Invoice\Enums\InvoiceType;
use DigitalTunnel\Invoice\Exceptions\InvalidInvoiceTypeException;
use DigitalTunnel\Invoice\Models\InvoiceScheme;

/**
 * Get the default invoice scheme
 */
function defaultScheme(): InvoiceScheme
{
    return InvoiceScheme::query()->first();
}

/**
 * Generate a new invoice number based on the given type
 *
 * @throws InvalidInvoiceTypeException
 */
function generateInvoiceNumber(InvoiceType $type): string
{
    return defaultScheme()->generateSerialNumber($type);
}

/**
 * Increment the serial number for the given type
 */
function incrementSerialNumber(InvoiceType $type): void
{
    defaultScheme()->increment($type->value.'_count');
}
