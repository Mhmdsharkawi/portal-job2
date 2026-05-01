<?php

namespace Database\Factories;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPosting>
 */
class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(),
            'company' => $this->faker->company(),
            'location' => $this->faker->city(),
            'salary' => $this->faker->numberBetween(1000, 10000),
            'category' => $this->faker->randomElement(['IT', 'Accounting', 'Design']),
            'user_id' => User::factory(),
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ];
    }
}
