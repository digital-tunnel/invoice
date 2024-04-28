<?php

namespace DigitalTunnel\Invoice\Classes;

use DigitalTunnel\Invoice\Traits\UsePdfFormat;
use Exception;

class PdfInvoiceItem
{
    use UsePdfFormat;

    /**
     * @throws Exception
     */
    public function __construct(
        public string $label,
        public ?int $unit_price = null,
        public ?int $unit_tax = null,
        public ?float $tax_percentage = null,
        public ?int $quantity = 1,
        public ?string $currency = null,
        public ?string $description = null,
    ) {

        if ($tax_percentage && ($tax_percentage > 100 || $tax_percentage < 0)) {
            throw new Exception("The tax_percentage parameter must be an integer between 0 and 100. $tax_percentage given.");
        }
    }

    public function subTotalAmount(): int
    {
        if ($this->unit_price === null) {
            return 0;
        }

        return $this->quantity !== null ? bcmul($this->unit_price, $this->quantity) : $this->unit_price;
    }

    public function totalTaxAmount(): int
    {
        if ($this->unit_tax) {
            return $this->quantity !== null ? bcmul($this->unit_tax, $this->quantity) : $this->unit_tax;
        }

        if ($this->tax_percentage) {
            // convert subtotal using native php function
            [$tax] = round($this->subTotalAmount(), 0, PHP_ROUND_HALF_UP);

            return $tax;
        }

        return 0;
    }

    public function totalAmount(): int
    {
        return bcadd($this->subTotalAmount(), $this->totalTaxAmount());
    }
}
