<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_listing(): void
    {
        $user = User::factory()->create([
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/job-listings', [
            'title' => 'Backend Developer',
            'description' => 'Role description',
            'company' => 'Acme Inc',
            'location' => 'Kampala',
            'salary' => 5000,
            'category' => 'IT',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'title', 'company']]);
    }

    public function test_unauthenticated_user_cannot_create_listing(): void
    {
        $response = $this->postJson('/api/job-listings', [
            'title' => 'Backend Developer',
            'description' => 'Role description',
            'company' => 'Acme Inc',
            'location' => 'Kampala',
            'salary' => 5000,
            'category' => 'IT',
        ]);

        $response->assertStatus(401);
    }

    public function test_owner_can_update_listing(): void
    {
        $owner = User::factory()->create([
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);
        $listing = JobPosting::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($owner, ['*']);

        $response = $this->putJson("/api/job-listings/{$listing->id}", [
            'title' => 'Updated title',
            'description' => $listing->description,
            'company' => $listing->company,
            'location' => $listing->location,
            'salary' => $listing->salary,
            'category' => $listing->category,
        ]);

        $response->assertOk()->assertJsonPath('data.title', 'Updated title');
    }

    public function test_non_owner_cannot_update_listing(): void
    {
        $owner = User::factory()->create(['role' => 'employer', 'email_verified_at' => now()]);
        $other = User::factory()->create(['role' => 'employer', 'email_verified_at' => now()]);
        $listing = JobPosting::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($other, ['*']);

        $response = $this->putJson("/api/job-listings/{$listing->id}", [
            'title' => 'Updated title',
            'description' => $listing->description,
            'company' => $listing->company,
            'location' => $listing->location,
            'salary' => $listing->salary,
            'category' => $listing->category,
        ]);

        $response->assertStatus(403);
    }

    public function test_listings_can_be_filtered_by_category(): void
    {
        $user = User::factory()->create(['role' => 'employer', 'email_verified_at' => now()]);
        JobPosting::factory()->create(['user_id' => $user->id, 'category' => 'IT']);
        JobPosting::factory()->create(['user_id' => $user->id, 'category' => 'Accounting']);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/job-listings?category=IT');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_listings_can_be_filtered_by_salary(): void
    {
        $user = User::factory()->create(['role' => 'employer', 'email_verified_at' => now()]);
        JobPosting::factory()->create(['user_id' => $user->id, 'salary' => 7000]);
        JobPosting::factory()->create(['user_id' => $user->id, 'salary' => 4000]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/job-listings?salary=7000');

        $response->assertOk()->assertJsonCount(1, 'data');
    }
}
