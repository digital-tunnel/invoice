<?php

namespace DigitalTunnel\Invoice\Models;

use DigitalTunnel\Invoice\Enums\InvoiceSchemeType;
use DigitalTunnel\Invoice\Enums\InvoiceType;
use DigitalTunnel\Invoice\Exceptions\InvalidInvoiceTypeException;
use DigitalTunnel\Invoice\Models\Scopes\IsDefault;
use Exception;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(IsDefault::class)]
class InvoiceScheme extends Model
{
    protected $fillable = [
        'name',
        'scheme_type',
        'prefix',
        'start_number',
        'invoice_count',
        'total_digits',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'scheme_type' => InvoiceSchemeType::class,
            'is_default' => 'boolean',
        ];
    }

    /**
     * Generate a serial number for the given invoice type
     *
     * @throws InvalidInvoiceTypeException
     */
    public function generateSerialNumber(InvoiceType $type): string
    {
        $serialNumber = '';

        if ($this->scheme_type === InvoiceSchemeType::Year) {
            $prefix = date('Y');
        } elseif ($this->scheme_type === InvoiceSchemeType::Prefix) {
            $prefix = $this->getPrefix($type);
        }

        $serialNumber .= $prefix;

        $count = $this->getNextSerial($type);
        if ($count === 0) {
            $count = $this->start_number;
        }

        $serialNumber .= str_pad($count, $this->total_digits + 3 - strlen($prefix), '0', STR_PAD_LEFT);

        return $serialNumber;
    }

    /**
     * Get the prefix for the given invoice type
     *
     * @throws Exception
     */
    private function getPrefix(InvoiceType $type): string
    {
        return match ($type) {
            InvoiceType::Invoice => $this->invoice_prefix,
            InvoiceType::Quote => $this->quote_prefix,
            InvoiceType::Credit => $this->credit_prefix,
            InvoiceType::Proforma => $this->proforma_prefix,
            default => throw new InvalidInvoiceTypeException('Invalid invoice type'),
        };
    }

    /**
     * Get the next serial number for the given invoice type
     *
     * @throws InvalidInvoiceTypeException
     */
    private function getNextSerial(InvoiceType $type): int
    {
        return match ($type) {
            InvoiceType::Invoice => $this->invoice_count + 1,
            InvoiceType::Quote => $this->quote_count + 1,
            InvoiceType::Credit => $this->credit_count + 1,
            InvoiceType::Proforma => $this->proforma_count + 1,
            default => throw new InvalidInvoiceTypeException('Invalid invoice type'),
        };
    }
}
