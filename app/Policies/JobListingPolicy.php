<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;

class JobListingPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'employer';
    }

    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $user->role === 'employer' && $jobPosting->user_id === $user->id;
    }

    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $user->role === 'employer' && $jobPosting->user_id === $user->id;
    }

    public function restore(User $user, JobPosting $jobPosting): bool
    {
        return $user->role === 'employer' && $jobPosting->user_id === $user->id;
    }
}
