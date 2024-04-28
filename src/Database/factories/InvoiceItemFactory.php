<?php

namespace DigitalTunnel\Invoice\Database\factories;

use DigitalTunnel\Invoice\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $price = fake()->numberBetween(1000, 100000);
        $unit_tax = fake()->numberBetween(0, $price);

        $useTaxPercentage = fake()->boolean();

        return [
            'label' => fake()->sentence(),
            'description' => fake()->sentence(),
            'unit_price' => $price,
            'currency' => 'EUR',
            'unit_tax' => ! $useTaxPercentage ? $unit_tax : null,
            'tax_percentage' => $useTaxPercentage ? fake()->numberBetween(0, 100) : null,
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}
