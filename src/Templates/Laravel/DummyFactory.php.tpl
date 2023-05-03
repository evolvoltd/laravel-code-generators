<?php

/** @var Factory $factory */

namespace Database\Factories;

use App\Models\Dummy;
//modelClassUsages
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DummyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Dummy::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }
}
