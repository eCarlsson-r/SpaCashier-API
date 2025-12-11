<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Journal;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition()
    {
        return [
            'reference' => $this->faker->unique()->uuid,
            'date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
        ];
    }
}
