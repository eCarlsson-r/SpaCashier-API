<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sales;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;

class SalesFactory extends Factory
{
    protected $model = Sales::class;

    public function definition()
    {
        return [
            'branch_id' => Branch::factory(),
            'customer_id' => Customer::factory(),
            'employee_id' => Employee::factory(),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'discount' => 0,
            'total' => $this->faker->numberBetween(100000, 500000),
        ];
    }
}
