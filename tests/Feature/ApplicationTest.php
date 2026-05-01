<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_apply_to_job(): void
    {
        $applicant = User::factory()->create([
            'role' => 'job_seeker',
            'email_verified_at' => now(),
        ]);
        $employer = User::factory()->create(['role' => 'employer', 'email_verified_at' => now()]);
        $job = JobPosting::factory()->create(['user_id' => $employer->id]);
        Sanctum::actingAs($applicant, ['*']);

        $response = $this->postJson('/api/apply', [
            'job_id' => $job->id,
            'cover_letter' => 'I am interested in this role.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'job_id', 'user_id', 'status']]);
    }

    public function test_user_cannot_apply_twice_to_same_job(): void
    {
        $applicant = User::factory()->create([
            'role' => 'job_seeker',
            'email_verified_at' => now(),
        ]);
        $employer = User::factory()->create(['role' => 'employer', 'email_verified_at' => now()]);
        $job = JobPosting::factory()->create(['user_id' => $employer->id]);
        JobApplication::factory()->create([
            'job_id' => $job->id,
            'user_id' => $applicant->id,
        ]);
        Sanctum::actingAs($applicant, ['*']);

        $response = $this->postJson('/api/apply', [
            'job_id' => $job->id,
            'cover_letter' => 'Trying to apply twice.',
        ]);

        $response->assertStatus(409)
            ->assertJson(['message' => 'You have already applied to this job']);
    }

    public function test_user_can_view_own_applications(): void
    {
        $applicant = User::factory()->create([
            'role' => 'job_seeker',
            'email_verified_at' => now(),
        ]);
        JobApplication::factory()->count(2)->create(['user_id' => $applicant->id]);
        Sanctum::actingAs($applicant, ['*']);

        $response = $this->getJson('/api/applications');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
