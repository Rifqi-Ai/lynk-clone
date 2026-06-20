<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $types = array_keys(Product::TYPES);
        $type = $this->faker->randomElement($types);
        $title = $this->faker->sentence(3);

        return [
            'id' => Product::generateId(),
            'user_id' => User::factory(),
            'type' => $type,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(10000, 500000),
            'compare_at_price' => null,
            'thumbnail_path' => null,
            'status' => 'draft',
            'sales_count' => 0,
            'view_count' => 0,
            'metadata' => [],
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'published']);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn (array $attrs) => [
            'type' => $type,
            'metadata' => match ($type) {
                'course' => ['modules' => ['Module 1', 'Module 2'], 'level' => 'beginner'],
                'event' => ['capacity' => 100, 'event_date' => now()->addWeek()->format('Y-m-d')],
                'donation' => ['preset_amounts' => [10000, 25000, 50000]],
                default => [],
            },
        ]);
    }
}
