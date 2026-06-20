<?php

namespace Database\Factories;

use App\Models\CourseModule;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModule>
 */
class CourseModuleFactory extends Factory
{
    protected $model = CourseModule::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'video_url' => null,
            'duration_minutes' => $this->faker->numberBetween(5, 60),
            'position' => $this->faker->numberBetween(1, 10),
            'is_published' => true,
            'is_free_preview' => false,
        ];
    }
}
