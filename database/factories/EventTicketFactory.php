<?php

namespace Database\Factories;

use App\Models\EventTicket;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventTicket>
 */
class EventTicketFactory extends Factory
{
    protected $model = EventTicket::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'buyer_email' => $this->faker->safeEmail(),
            'attendee_name' => $this->faker->name(),
            'ticket_code' => 'TKT-'.strtoupper($this->faker->bothify('??####')),
            'is_checked_in' => false,
            'checked_in_at' => null,
            'checked_in_by' => null,
        ];
    }
}
