<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Shift;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition()
    {
        return [
            'id' => $this->faker->randomElement(['M', 'A', 'N', 'D']),
            'name' => $this->faker->word(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ];
    }
}
