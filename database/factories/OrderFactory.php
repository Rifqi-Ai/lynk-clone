<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(10000, 500000);
        $feePct = 10;
        $feeAmount = $subtotal * ($feePct / 100);
        $total = $subtotal + $feeAmount;
        $payout = $subtotal; // simplified

        return [
            'id' => 'ORD-'.now()->format('Ymd').'-'.strtoupper($this->faker->bothify('????####')),
            'product_id' => Product::factory(),
            'creator_user_id' => User::factory(),
            'buyer_user_id' => null,
            'buyer_email' => $this->faker->safeEmail(),
            'unit_price' => $subtotal,
            'quantity' => 1,
            'subtotal' => $subtotal,
            'fee_pct' => $feePct,
            'fee_amount' => $feeAmount,
            'total' => $total,
            'creator_payout' => $payout,
            'payment_status' => 'pending',
            'expired_at' => now()->addHours(24),
            'metadata' => [],
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'VC',
            'duitku_reference' => 'DUI'.$this->faker->bothify('########'),
        ]);
    }
}
