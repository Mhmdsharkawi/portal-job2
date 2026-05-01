<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_id' => JobPosting::factory(),
            'cover_letter' => $this->faker->paragraph(),
            'status' => 'pending',
            'resume_path' => null,
        ];
    }
}
