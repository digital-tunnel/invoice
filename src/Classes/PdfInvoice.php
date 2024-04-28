<?php

namespace DigitalTunnel\Invoice\Classes;

use Carbon\Carbon;
use DigitalTunnel\Invoice\Traits\UsePdfFormat;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PdfInvoice
{
    use UsePdfFormat;

    /**
     * @param  null|PdfInvoiceItem[]  $items
     * @param  null|InvoiceDiscount[]  $discounts
     */
    public function __construct(
        public ?string $name = null,
        public ?string $state = null,
        public ?string $serial_number = null,
        public ?array $buyer = null,
        public ?Carbon $due_at = null,
        public ?Carbon $created_at = null,
        public ?Carbon $paid_at = null,
        public ?array $seller = null,
        public ?string $description = null,
        public ?string $logo = null,
        public ?string $template = null,
        public ?string $filename = null,
        public ?array $items = null,
        public ?string $tax_label = null,
        public ?array $discounts = null
    ) {
        $this->name = $name ?? __('invoices::invoice.invoice');
        $this->seller = $seller ?? config('invoices.default_seller', []);
        $this->logo = $logo ?? config('invoices.default_logo', null);
        $this->template = sprintf('invoices::%s', $template ?? config('invoices.default_template', null));
    }

    public function generateFilename(): string
    {
        return Str::slug("{$this->name}_{$this->serial_number}", separator: '_').'.pdf';
    }

    public function getFilename(): string
    {
        return $this->filename ?? $this->generateFilename();
    }

    public function getCurrency(): string
    {
        /** @var ?PdfInvoiceItem $firstItem */
        $firstItem = Arr::first($this->items);

        return $firstItem?->currency ?? config('invoices.default_currency');
    }

    public function getLogo(): string
    {
        $type = pathinfo($this->logo, PATHINFO_EXTENSION);
        $data = file_get_contents($this->logo);

        return 'data:image/'.$type.';base64,'.base64_encode($data);
    }

    /**
     * Before discount and taxes
     */
    public function subTotalAmount(): int
    {
        return array_reduce(
            $this->items,
            fn ($total, PdfInvoiceItem $item) => bcadd($item->subTotalAmount(), $total, 2),
            0
        );
    }

    public function totalTaxAmount(): int
    {
        return array_reduce(
            $this->items,
            fn ($total, PdfInvoiceItem $item) => bcadd($item->totalTaxAmount(), $total, 2),
            0
        );
    }

    public function totalDiscountAmount(): int
    {
        if (! $this->discounts) {
            return 0;
        }

        $subtotal = $this->subTotalAmount();

        return array_reduce($this->discounts, function (int $total, InvoiceDiscount $discount) use ($subtotal) {
            return bcadd($total, $discount->computeDiscountAmountOn($subtotal), 2);
        }, 0);
    }

    public function totalAmount(): int
    {
        $total = array_reduce(
            $this->items,
            fn (int $total, PdfInvoiceItem $item) => bcadd($item->totalAmount(), $total, 2),
            0
        );

        return bcsub($total, $this->totalDiscountAmount(), 2);
    }

    public function pdf(): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::setPaper(
            config('invoices.paper_options.paper', 'a4'),
            config('invoices.paper_options.orientation', 'portrait')
        );

        foreach (config('invoices.pdf_options') as $attribute => $value) {
            $pdf->setOption($attribute, $value);
        }

        return $pdf->loadView($this->template, ['invoice' => $this]);
    }

    public function stream(): Response
    {
        return $this->pdf()->stream($this->getFilename());
    }

    public function download(): Response
    {
        return $this->pdf()->download($this->getFilename());
    }

    public function view(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    {
        return view($this->template, ['invoice' => $this]);
    }
}
