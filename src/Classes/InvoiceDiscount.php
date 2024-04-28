<?php

namespace DigitalTunnel\Invoice\Classes;

use DigitalTunnel\Invoice\Traits\UsePdfFormat;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class InvoiceDiscount implements Arrayable, JsonSerializable
{
    use UsePdfFormat;

    public function __construct(
        public ?string $name = null,
        public ?string $code = null,
        public ?int $amount_off = null,
        public ?float $percent_off = null,
    ) {
        // code...
    }

    public function computeDiscountAmountOn(int $amout): int
    {
        if ($this->amount_off) {
            return $this->amount_off;
        }

        if (! is_null($this->percent_off)) {
            return round($amout * $this->percent_off / 100, 2, PHP_ROUND_HALF_UP);
        }

        return 0;
    }

    public static function fromArray(?array $array): static
    {
        $currency = data_get($array, 'currency', config('invoices.default_currency'));
        $amount_off = data_get($array, 'amount_off');
        $percent_off = data_get($array, 'percent_off');

        return new static(
            name: data_get($array, 'name'),
            code: data_get($array, 'code'),
            amount_off: $amount_off ?: null,
            percent_off: $percent_off ? (float) $percent_off : null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'amount_off' => $this->amount_off,
            'currency' => 'SAR',
            'percent_off' => $this->percent_off,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toLivewire(): array
    {
        return $this->toArray();
    }

    public static function fromLivewire($value): static
    {
        return static::fromArray($value);
    }
}
