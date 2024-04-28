<?php

namespace DigitalTunnel\Invoice\Database\factories;

use DigitalTunnel\Invoice\Enums\InvoiceState;
use DigitalTunnel\Invoice\Enums\InvoiceType;
use DigitalTunnel\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $created_at = fake()->dateTime();

        return [
            'type' => InvoiceType::Invoice,
            'state' => fake()->randomElement(InvoiceState::cases()),
            'updated_at' => fake()->dateTimeBetween($created_at),
            'created_at' => $created_at,
            'due_at' => fake()->dateTimeBetween($created_at, '+ 30 days'),
            'description' => fake()->sentence(),
            'buyer_information' => [
                'name' => fake()->company(),
                'address' => [
                    'street' => fake()->streetName(),
                    'city' => fake()->city(),
                    'postal_code' => fake()->postcode(),
                    'state' => null,
                    'country' => fake()->country(),
                ],
                'email' => fake()->email(),
                'phone_number' => fake()->phoneNumber(),
                'tax_number' => fake()->numberBetween(12345678, 99999999),
            ],
        ];
    }

    public function quote(): static
    {
        return $this->state([
            'type' => InvoiceType::Quote,
        ]);
    }

    public function proforma(): static
    {
        return $this->state([
            'type' => InvoiceType::Proforma,
        ]);
    }

    public function invoice(): static
    {
        return $this->state([
            'type' => InvoiceType::Invoice,
        ]);
    }

    public function credit(): static
    {
        return $this->state([
            'type' => InvoiceType::Credit,
        ]);
    }
}
