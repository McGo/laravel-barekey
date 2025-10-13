<?php

namespace McGo\Barekey\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use McGo\Barekey\Models\ApiKey;

class ApiKeyFactory extends Factory
{

    protected $model = ApiKey::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'abilities' => []
        ];
    }
}