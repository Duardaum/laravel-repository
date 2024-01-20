<?php

namespace Duardaum\LaravelRepository\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Duardaum\LaravelRepository\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => Str::random(50),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

}
